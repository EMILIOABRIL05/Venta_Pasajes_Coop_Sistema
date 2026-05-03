<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Ruta;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;


class VentaController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * Muestra el listado de rutas disponibles y el mapa de asientos.
     */
    public function index()
    {
        $rutas = Ruta::with(['origen', 'destino'])
            ->orderBy('precio_base')
            ->get();

        return view('ventanilla.index', compact('rutas'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    /**
     * Registra la cabecera de venta y sus boletos en una transacción atómica.
     *
     * Estrategia de resiliencia:
     *  - DB::transaction(..., 2)  → reintenta 2 veces ante deadlocks de InnoDB.
     *  - QueryException           → errores de DB conocidos (FK, constraint, etc.).
     *  - Throwable                → cualquier fallo inesperado (red, PHP fatal, etc.).
     *
     * En ambos casos la transacción hace ROLLBACK automático, garantizando
     * que nunca quede una Venta sin sus Boletos correspondientes.
     */
    public function store(Request $request)
    {
        // ── 1. Validación estricta de entrada ─────────────────────────────────
        //    'distinct' rechaza que el frontend envíe el mismo número dos veces.
        $validated = $request->validate([
            'ruta_id'         => ['required', 'integer', 'exists:rutas,id'],
            'pasajero_id'     => ['required', 'integer', 'exists:pasajeros,id'],
            'asientos'        => ['required', 'array', 'min:1', 'max:40'],
            'asientos.*'      => ['required', 'integer', 'between:1,40', 'distinct'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01'],
        ]);

        // ── 2. Preparación de datos (fuera del lock transaccional) ────────────
        //    Toda operación que NO requiera acceso a la BD debe hacerse aquí,
        //    para minimizar el tiempo que los registros quedan bloqueados.
        $asientos       = array_values(array_unique($validated['asientos']));
        $precioUnitario = (float) $validated['precio_unitario'];
        $total          = round($precioUnitario * count($asientos), 2);

        //    Payload para createMany(): cada elemento es un array con los campos
        //    del boleto. El UUID se genera en Boleto::booted() durante el create().
        //    NO se incluye 'venta_id' porque Eloquent lo inyecta a través de
        //    la relación hasMany ($venta->boletos()->createMany(...)).
        $boletosPayload = array_map(fn (int $seat) => [
            'pasajero_id'    => $validated['pasajero_id'],  // FK → pasajeros.id ✓
            'numero_asiento' => (string) $seat,
            'precio_final'   => $precioUnitario,
        ], $asientos);

        // ── 2.5 Verificación de disponibilidad en tiempo real ─────────────────
        //    Doble capa de protección contra ventas simultáneas del mismo asiento:
        //      a) Cache::lock()  → bloqueo atómico (evita race conditions)
        //      b) Query 60s      → red de seguridad contra boletos recién creados
        [$conflict, $locks] = $this->verifyAsientosDisponibles($asientos);

        if ($conflict !== null) {
            return back()
                ->withInput()
                ->with('error', $conflict);
        }

        // ── 3. Transacción atómica con reintentos ante deadlock ───────────────
        try {
            $venta = DB::transaction(function () use ($boletosPayload, $total) {

                // 3a. Cabecera de la venta ─────────────────────────────────────
                //     FK: ventas.user_id → users.id (cajero autenticado)
                $venta = Venta::create([
                    'user_id' => auth()->id(),
                    'total'   => $total,
                ]);

                // 3b. Inserción masiva de boletos ──────────────────────────────
                //     createMany() itera el payload y llama create() por cada
                //     elemento, disparando Boleto::booted() → UUID automático.
                //
                //     Garantías de integridad referencial:
                //       · boletos.venta_id    → ventas.id    ✓ vía relación Eloquent
                //       · boletos.pasajero_id → pasajeros.id ✓ validado con exists:
                //
                //     Si CUALQUIER insert falla (FK rota, duplicado, etc.),
                //     Eloquent lanza una excepción → MySQL ejecuta ROLLBACK
                //     completo → ni la Venta ni ningún Boleto queda persistido.
                $venta->boletos()->createMany($boletosPayload);

                // Devolver el modelo hidratado (sin query adicional al cliente)
                return $venta->load('boletos');

            }, 2); // ← 2 reintentos automáticos ante deadlock de InnoDB

        } catch (QueryException $e) {

            Log::error('[Ventanilla] store() — QueryException', [
                'user_id'  => auth()->id(),
                'asientos' => $asientos,
                'total'    => $total,
                'sql'      => $e->getSql(),
                'bindings' => $e->getBindings(),
                'message'  => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error de base de datos al registrar la venta. Intente nuevamente.');

        } catch (Throwable $e) {
            Log::critical('[Ventanilla] store() — Fallo crítico inesperado', [
                'user_id'  => auth()->id(),
                'asientos' => $asientos ?? [],
                'total'    => $total    ?? 0,
                'message'  => $e->getMessage(),
                'file'     => $e->getFile().':'.$e->getLine(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error inesperado. Contacte al administrador del sistema.');

        } finally {
            // ── Siempre liberar los Cache locks ──────────────────────────────
            //    El bloque finally se ejecuta tanto si la transacción fue exitosa
            //    como si lanzó cualquier excepción, garantizando que los asientos
            //    queden disponibles para el siguiente intento.
            collect($locks)->each(fn ($lock) => $lock->release());
        }

        // ── 4. Respuesta de éxito: Flash estructurado ─────────────────────────
        //    Array en sesión en lugar de string para que la vista construya
        //    un resumen visual rico con todos los detalles de la operación.
        return redirect()
            ->route('ventanilla.index')
            ->with('venta_exitosa', [
                'id'       => $venta->id,
                'total'    => number_format($total, 2),
                'boletos'  => $venta->boletos->count(),
                'asientos' => $venta->boletos
                                   ->pluck('numero_asiento')
                                   ->sort()->values()->join(', '),
                'codigos'  => $venta->boletos
                                   ->pluck('codigo_reserva')
                                   ->join(' · '),
                'cajero'   => auth()->user()->name ?? 'Sistema',
                'fecha'    => now()->format('d/m/Y'),
                'hora'     => now()->format('H:i:s'),
            ]);
    }


    // ─── Métodos pendientes de implementar ────────────────────────────────────

    public function create()
    {
        $rutas = Ruta::with(['origen', 'destino'])
            ->orderBy('precio_base')
            ->get();

        return view('ventanilla.ventas.create', compact('rutas'));
    }


    public function show(Venta $venta)
    {
        $venta->load('boletos.pasajero');

        return view('ventanilla.ventas.show', compact('venta'));
    }

    public function edit(Venta $venta) {}

    public function update(Request $request, Venta $venta) {}

    public function destroy(Venta $venta) {}

    // ─── Privados ─────────────────────────────────────────────────────────────

    /**
     * Verifica que ningún asiento del array esté bloqueado o vendido
     * en los últimos 60 segundos por otro usuario.
     *
     * Estrategia de doble capa:
     *   1. Cache::lock()  — bloqueo atómico en memoria: impide que dos requests
     *                       concurrentes procesen el mismo asiento a la vez.
     *   2. Query BD 60s   — red de seguridad: detecta boletos creados muy
     *                       recientemente aunque el lock ya se haya liberado.
     *
     * @param  int[]  $asientos  Números de asiento a verificar
     * @return array{0: string|null, 1: array}  [mensaje_error|null, locks_adquiridos]
     */
    private function verifyAsientosDisponibles(array $asientos): array
    {
        $LOCK_TTL  = 60; // segundos
        $WINDOW_S  = 60; // ventana de detección en BD

        // ── Capa 1: Cache locks atómicos ──────────────────────────────────────
        $locks      = [];
        $bloqueados = [];

        foreach ($asientos as $seat) {
            $lock = Cache::lock("asiento:{$seat}", $LOCK_TTL);

            if ($lock->get()) {
                $locks[] = $lock;          // Adquirido: guardar para liberar luego
            } else {
                $bloqueados[] = $seat;     // Otro cajero lo está procesando ahora
            }
        }

        if (!empty($bloqueados)) {
            // Liberar los locks que SÍ adquirimos antes de abortar
            collect($locks)->each(fn ($l) => $l->release());

            $lista = implode(', ', array_map(fn ($s) => "#{$s}", $bloqueados));
            return [
                "Los asientos {$lista} están siendo procesados por otro cajero. Intente en {$LOCK_TTL} segundos.",
                [],
            ];
        }

        // ── Capa 2: Ventana de 60s en BD ──────────────────────────────────────
        //    Detecta boletos creados recientemente aunque el lock ya se liberó
        //    (p.ej. venta exitosa hace 30s → lock liberado → asiento bloqueado aún).
        $vendidosReciente = Boleto::whereIn(
                'numero_asiento',
                array_map('strval', $asientos)
            )
            ->where('created_at', '>=', now()->subSeconds($WINDOW_S))
            ->pluck('numero_asiento');

        if ($vendidosReciente->isNotEmpty()) {
            collect($locks)->each(fn ($l) => $l->release());

            $lista = $vendidosReciente->map(fn ($s) => "#{$s}")->join(', ');
            return [
                "Los asientos {$lista} ya fueron vendidos en los últimos {$WINDOW_S} segundos. Seleccione otros asientos.",
                [],
            ];
        }

        // Sin conflictos: devolver locks para que el llamador los libere en finally
        return [null, $locks];
    }
}


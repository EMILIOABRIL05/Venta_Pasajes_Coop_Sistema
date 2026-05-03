<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
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

    public function create() {}

    public function show(Venta $venta)
    {
        $venta->load('boletos.pasajero');

        return view('ventanilla.ventas.show', compact('venta'));
    }

    public function edit(Venta $venta) {}

    public function update(Request $request, Venta $venta) {}

    public function destroy(Venta $venta) {}
}


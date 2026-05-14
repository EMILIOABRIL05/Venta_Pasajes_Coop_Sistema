<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Exports\CierreTurnoExport;
use App\Models\Boleto;
use App\Models\CierreTurno;
use App\Models\Ruta;
use App\Models\Venta;
use App\Services\CierreTurnoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;


class VentaController extends Controller
{
    // ─── Inyección de dependencia (DIP) ───────────────────────────────────────
    public function __construct(
        private readonly CierreTurnoService $cierreService,
    ) {}
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
        $frecuencia = \App\Models\Frecuencia::where('ruta_id', $validated['ruta_id'])->first();
        
        $boletosPayload = array_map(fn (int $seat) => [
            'pasajero_id'    => $validated['pasajero_id'],  // FK → pasajeros.id ✓
            'frecuencia_id'  => $frecuencia ? $frecuencia->id : null,
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
            $sqlState = $e->getCode();

            $logContext = [
                'sqlstate'  => $sqlState,
                'sql'       => $e->getSql(),
                'bindings'  => $e->getBindings(),
                'message'   => $e->getMessage(),
                'user'      => [
                    'id'    => auth()->id(),
                    'name'  => auth()->user()->name  ?? 'N/A',
                    'email' => auth()->user()->email ?? 'N/A',
                ],
                'request'   => [
                    'ip'      => $request->ip(),
                    'url'     => $request->fullUrl(),
                    'payload' => [
                        'ruta_id'     => $validated['ruta_id']     ?? null,
                        'pasajero_id' => $validated['pasajero_id'] ?? null,
                        'asientos'    => $asientos                 ?? [],
                        'total'       => $total                    ?? 0,
                    ],
                ],
            ];

            if ($sqlState === '23000') {
                Log::error('[Ventanilla] store() — Integrity constraint violation', $logContext);
                return back()
                    ->withInput()
                    ->with('error', 'Error de integridad en base de datos. Uno de los datos ingresados viola una restricción del sistema (llave foránea o valor duplicado). Verifique los datos del pasajero y la ruta seleccionada.');
            } elseif ($sqlState === '40001') {
                Log::warning('[Ventanilla] store() — Deadlock tras reintentos', $logContext);
                return back()
                    ->withInput()
                    ->with('error', 'Conflicto de concurrencia. Otro cajero procesó una venta al mismo tiempo. Espere unos segundos e intente de nuevo.');
            } else {
                Log::error('[Ventanilla] store() — QueryException genérica', $logContext);
                return back()
                    ->withInput()
                    ->with('error', 'Ocurrió un problema al guardar la venta en la base de datos. Intente nuevamente o contacte al soporte técnico.');
            }

        } catch (Throwable $e) {
            Log::critical('[Ventanilla] store() — Fallo crítico inesperado', [
                'message'   => $e->getMessage(),
                'file'      => $e->getFile() . ':' . $e->getLine(),
                'trace'     => $e->getTraceAsString(),
                'user'      => [
                    'id'    => auth()->id(),
                    'name'  => auth()->user()->name  ?? 'N/A',
                    'email' => auth()->user()->email ?? 'N/A',
                ],
                'request'   => [
                    'ip'      => $request->ip(),
                    'url'     => $request->fullUrl(),
                    'payload' => [
                        'asientos' => $asientos ?? [],
                        'total'    => $total    ?? 0,
                    ],
                ],
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error inesperado del sistema. La operación fue cancelada de forma segura. Contacte al administrador e indíquele la hora exacta: ' . now()->format('H:i:s d/m/Y') . '.');

        } finally {
            // ── Siempre liberar los Cache locks ──────────────────────────────
            //    El bloque finally se ejecuta tanto si la transacción fue exitosa
            //    como si lanzó cualquier excepción, garantizando que los asientos
            //    queden disponibles para el siguiente intento.
            collect($locks)->each(fn ($lock) => $lock->release());
        }

        // ── 3.5 Enviar correos de confirmación en segundo plano ───────────────
        $venta->load('boletos.pasajero');
        foreach ($venta->boletos as $boleto) {
            if (!empty($boleto->pasajero->correo)) {
                \Illuminate\Support\Facades\Mail::to($boleto->pasajero->correo)
                    ->queue(new \App\Mail\BoletoVendido($boleto));
            }
        }

        // ── 4. Respuesta de éxito: Flash estructurado ─────────────────────────
        //    Array en sesión en lugar de string para que la vista construya
        //    un resumen visual rico con todos los detalles de la operación.
        return redirect()
            ->route('ventanilla.ventas.index')
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

    /**
     * Muestra el formulario para crear una nueva venta de boletos.
     * Carga las rutas disponibles para el selector.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $rutas = Ruta::with(['origen', 'destino'])
            ->orderBy('precio_base')
            ->get();

        return view('ventanilla.ventas.create', compact('rutas'));
    }

    /**
     * Muestra los detalles de una venta específica y sus boletos.
     *
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\View\View
     */
    public function show(Venta $venta)
    {
        $venta->load('boletos.pasajero');

        return view('ventanilla.ventas.show', compact('venta'));
    }

    /**
     * Muestra el formulario para editar una venta.
     * (Método pendiente de implementar)
     *
     * @param  \App\Models\Venta  $venta
     * @return void
     */
    public function edit(Venta $venta) {}

    /**
     * Actualiza los datos de una venta en almacenamiento.
     * (Método pendiente de implementar)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Venta  $venta
     * @return void
     */
    public function update(Request $request, Venta $venta) {}

    /**
     * Elimina una venta del almacenamiento (soft delete).
     * (Método pendiente de implementar)
     *
     * @param  \App\Models\Venta  $venta
     * @return void
     */
    public function destroy(Venta $venta) {}

    /**
     * Anula un boleto específico, cambiando su estado a 'Anulado',
     * registrando la fecha de cancelación y liberando el asiento.
     * Usa DB::transaction para garantizar la integridad.
     *
     * @param string $id UUID del boleto
     * @return \Illuminate\Http\RedirectResponse
     */
    public function anularBoleto($id)
    {
        try {
            $boleto = Boleto::findOrFail($id);

            // Regla de negocio: Límite de 30 minutos para anular
            if ($boleto->created_at->diffInMinutes(now()) > 30) {
                return back()->with('error', 'Tiempo límite de anulación excedido.');
            }

            DB::transaction(function () use ($boleto) {
                // 1. Cambiar estado a 'Anulado'
                $boleto->estado = 'Anulado';
                
                // 2. Registrar la fecha de cancelación
                $boleto->fecha_cancelacion = now();
                $boleto->save();

                // 3. Liberar el asiento en la base de datos
                // El trait SoftDeletes establece deleted_at, lo que excluye al
                // boleto de las consultas normales de disponibilidad.
                $boleto->delete();
            });

            return back()->with('success', 'Boleto anulado correctamente. El asiento ha sido liberado.');
        } catch (\Exception $e) {
            Log::error('[Ventanilla] Error al anular boleto', [
                'boleto_id' => $id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'Ocurrió un problema al anular el boleto. Verifique que el boleto exista e inténtelo de nuevo.');
        }
    }

    // ─── Cierre de turno ──────────────────────────────────────────────────────

    /**
     * Dashboard de Cierre de Turno (Sprint 4 - Manolo).
     *
     * Delega todos los cálculos a CierreTurnoService (SRP).
     * Los datos están aislados por user_id en el propio Service (DIP).
     */
    public function cierreTurno()
    {
        $hoy    = now()->toDateString();
        $userId = auth()->id();

        $resumen = $this->cierreService->resumenCompleto($userId, $hoy);

        return view('ventanilla.cierre', array_merge($resumen, ['fecha' => $hoy]));
    }

    /**
     * Persiste el cierre de turno del cajero autenticado (POST).
     *
     * Flujo:
     *  1. Guarda de duplicado — usa CierreTurno::existeParaHoy().
     *  2. Obtiene totales del turno desde CierreTurnoService (SRP/DIP).
     *  3. Crea el registro en DB::transaction() (atomicidad obligatoria, ver AGENTS.md).
     *  4. Redirige con flash de éxito o back() con flash de error.
     */
    public function storeCierre(Request $request)
    {
        $userId = auth()->id();
        $hoy    = now()->toDateString();

        // ── 1. Guardia contra duplicado ───────────────────────────────────────
        if (CierreTurno::existeParaHoy($userId, $hoy)) {
            return back()->with(
                'error',
                'Error: Ya has registrado un cierre de caja para el turno de hoy.'
            );
        }

        // ── 2. Obtener totales del servicio (sin lógica duplicada) ────────────
        $resumen = $this->cierreService->resumenCompleto($userId, $hoy);

        // ── 3. Persistencia atómica ───────────────────────────────────────────
        try {
            DB::transaction(function () use ($userId, $hoy, $resumen) {
                // Segunda verificación dentro de la transacción para evitar
                // condición de carrera si dos requests llegan casi simultáneamente.
                if (CierreTurno::existeParaHoy($userId, $hoy)) {
                    throw new \RuntimeException('duplicate_cierre');
                }

                CierreTurno::create([
                    'user_id'          => $userId,
                    'fecha'            => $hoy,
                    'total_bruto'      => $resumen['totalBruto'],
                    'total_reembolsos' => $resumen['totalReembolsos'],
                    'total_neto'       => $resumen['totalNeto'],
                    'total_boletos'    => $resumen['totalBoletos'],
                ]);
            });

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'duplicate_cierre') {
                return back()->with(
                    'error',
                    'Error: Ya has registrado un cierre de caja para el turno de hoy.'
                );
            }
            Log::error('[Ventanilla] storeCierre() — RuntimeException inesperada', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'fecha'   => $hoy,
            ]);
            return back()->with('error', 'Ocurrió un error inesperado. La operación fue cancelada de forma segura.');

        } catch (Throwable $e) {
            Log::critical('[Ventanilla] storeCierre() — Fallo crítico', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'user_id' => $userId,
                'fecha'   => $hoy,
            ]);
            return back()->with(
                'error',
                'Error al guardar el cierre. Contacte al administrador e indique la hora: ' . now()->format('H:i:s d/m/Y') . '.'
            );
        }

        // ── 4. Respuesta de éxito ─────────────────────────────────────────────
        return redirect()
            ->route('ventanilla.cierre')
            ->with('success', '¡Cierre de turno guardado exitosamente!');
    }

    /**
     * Genera y descarga el reporte PDF del cierre de turno.
     * Los datos se obtienen del CierreTurnoService — sin lógica duplicada.
     */
    public function reportePdf()
    {
        $cajero  = auth()->user();
        $hoy     = now()->toDateString();
        $resumen = $this->cierreService->resumenCompleto($cajero->id, $hoy);

        $pdf = Pdf::loadView(
            'ventanilla.reporte_pdf',
            array_merge($resumen, ['cajero' => $cajero, 'fecha' => $hoy])
        )->setPaper('letter', 'portrait');

        return $pdf->download(
            'cierre_turno_' . str_replace(' ', '_', strtolower($cajero->name)) . "_{$hoy}.pdf"
        );
    }

    /**
     * Exporta el cierre del turno a Excel (.xlsx) con dos hojas.
     * CierreTurnoExport reutiliza CierreTurnoService internamente.
     */
    public function exportarExcel()
    {
        $cajero = auth()->user();
        $hoy    = now()->toDateString();

        return Excel::download(
            new CierreTurnoExport($cajero->id, $hoy, $cajero->name),
            'cierre_turno_' . str_replace(' ', '_', strtolower($cajero->name)) . "_{$hoy}.xlsx"
        );
    }

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


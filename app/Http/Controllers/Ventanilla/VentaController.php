<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\CierreTurno;
use App\Models\Reembolso;
use App\Models\Ruta;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Dashboard de Cierre de Turno (Sprint 4 - Manolo).
     *
     * Calcula los KPIs del turno actual del cajero autenticado:
     *   - Total cobrado (ingreso bruto)
     *   - Boletos vendidos
     *   - Promedio de venta por boleto
     *   - Total reembolsos aprobados
     *   - Ingreso neto (bruto - reembolsos)
     *   - Desglose por ruta
     *   - Estado del cierre (si ya fue registrado hoy)
     */
    public function cierreTurno()
    {
        $userId = auth()->id();
        $hoy    = now()->toDateString();

        // ── ¿Ya existe un cierre registrado para hoy? ─────────────────────────
        $cierreExistente = CierreTurno::where('user_id', $userId)
            ->where('fecha', $hoy)
            ->first();

        // ── Ventas del día con relaciones necesarias ──────────────────────────
        $ventas = Venta::with([
                'boletos.frecuencia.ruta.origen',
                'boletos.frecuencia.ruta.destino',
                'reembolsos',
            ])
            ->where('user_id', $userId)
            ->whereDate('created_at', $hoy)
            ->get();

        // ── KPI: Ingreso bruto ────────────────────────────────────────────────
        $totalBruto   = (float) $ventas->sum('total');
        $totalBoletos = (int)   $ventas->sum(fn (Venta $v) => $v->boletos->count());

        // ── KPI: Reembolsos aprobados ─────────────────────────────────────────
        $totalReembolsos = (float) $ventas->sum(
            fn (Venta $v) => $v->reembolsos
                ->where('estado', 'aprobado')
                ->sum('monto')
        );

        // ── KPI: Ingreso neto ─────────────────────────────────────────────────
        $totalNeto = $totalBruto - $totalReembolsos;

        // ── KPI: Promedio de venta por boleto ─────────────────────────────────
        $promedioPorBoleto = $totalBoletos > 0
            ? round($totalBruto / $totalBoletos, 2)
            : 0.0;

        // ── Desglose por Ruta ─────────────────────────────────────────────────
        $recaudacionPorRuta = $ventas
            ->flatMap(fn (Venta $venta) =>
                $venta->boletos->map(fn ($boleto) => [
                    'ruta_id'      => optional(optional($boleto->frecuencia)->ruta)->id,
                    'ruta_nombre'  => $boleto->frecuencia && $boleto->frecuencia->ruta
                        ? (optional($boleto->frecuencia->ruta->origen)->nombre ?? '—')
                          . ' → '
                          . (optional($boleto->frecuencia->ruta->destino)->nombre ?? '—')
                        : 'Sin ruta',
                    'venta_total'  => (float) $venta->total,
                ])
            )
            ->groupBy('ruta_id')
            ->map(fn ($grupo) => [
                'ruta'            => $grupo->first()['ruta_nombre'],
                'total_recaudado' => round($grupo->sum('venta_total'), 2),
                'boletos_count'   => $grupo->count(),
            ])
            ->values();

        // ── Últimas 10 transacciones (tabla histórica) ────────────────────────
        $ultimasTransacciones = $ventas
            ->sortByDesc('created_at')
            ->take(10)
            ->map(fn (Venta $v) => [
                'id'         => $v->id,
                'hora'       => $v->created_at->format('H:i'),
                'total'      => (float) $v->total,
                'boletos'    => $v->boletos->count(),
                'asientos'   => $v->boletos->pluck('numero_asiento')->sort()->join(', '),
                'pasajero'   => optional($v->boletos->first()?->pasajero)->nombre_completo ?? '—',
                'ruta'       => $v->boletos->first()?->frecuencia?->ruta
                    ? (optional($v->boletos->first()->frecuencia->ruta->origen)->nombre ?? '—')
                      . ' → '
                      . (optional($v->boletos->first()->frecuencia->ruta->destino)->nombre ?? '—')
                    : 'Sin ruta',
            ])
            ->values();

        // ── Progresión horaria (para Chart.js) ───────────────────────────────
        // Crea 24 slots (0-23h). Cada slot acumula total y boletos_count.
        $slotsHorarios = collect(range(0, 23))->mapWithKeys(fn ($h) => [
            $h => ['total' => 0.0, 'boletos' => 0, 'label' => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'],
        ]);

        foreach ($ventas as $v) {
            $hora = (int) $v->created_at->format('G'); // 0-23
            $slotsHorarios[$hora]['total']   += (float) $v->total;
            $slotsHorarios[$hora]['boletos'] += $v->boletos->count();
        }

        // Solo enviar al frontend los datos que Chart.js necesita
        $chartHorario = [
            'labels'  => $slotsHorarios->pluck('label')->values()->toArray(),
            'totales' => $slotsHorarios->pluck('total')->map(fn ($v) => round($v, 2))->values()->toArray(),
            'boletos' => $slotsHorarios->pluck('boletos')->values()->toArray(),
        ];

        return view('ventanilla.cierre', [
            'cierreExistente'       => $cierreExistente,
            'totalBruto'            => $totalBruto,
            'totalNeto'             => $totalNeto,
            'totalReembolsos'       => $totalReembolsos,
            'totalBoletos'          => $totalBoletos,
            'promedioPorBoleto'     => $promedioPorBoleto,
            'recaudacionPorRuta'    => $recaudacionPorRuta,
            'ultimasTransacciones'  => $ultimasTransacciones,
            'chartHorario'          => $chartHorario,
            'fecha'                 => $hoy,
        ]);
    }

    /**
     * Genera y descarga el reporte de cierre de turno en PDF (DomPDF).
     *
     * Reutiliza la misma lógica de cálculo de cierreTurno() para garantizar
     * que los datos del PDF coincidan exactamente con el dashboard en pantalla.
     */
    public function reportePdf()
    {
        $userId = auth()->id();
        $cajero = auth()->user();
        $hoy    = now()->toDateString();

        $cierreExistente = CierreTurno::where('user_id', $userId)
            ->where('fecha', $hoy)
            ->first();

        $ventas = Venta::with([
                'boletos.frecuencia.ruta.origen',
                'boletos.frecuencia.ruta.destino',
                'boletos.pasajero',
                'reembolsos',
            ])
            ->where('user_id', $userId)
            ->whereDate('created_at', $hoy)
            ->get();

        $totalBruto      = (float) $ventas->sum('total');
        $totalBoletos    = (int)   $ventas->sum(fn (Venta $v) => $v->boletos->count());
        $totalReembolsos = (float) $ventas->sum(
            fn (Venta $v) => $v->reembolsos->where('estado', 'aprobado')->sum('monto')
        );
        $totalNeto         = $totalBruto - $totalReembolsos;
        $promedioPorBoleto = $totalBoletos > 0 ? round($totalBruto / $totalBoletos, 2) : 0.0;

        $recaudacionPorRuta = $ventas
            ->flatMap(fn (Venta $venta) =>
                $venta->boletos->map(fn ($b) => [
                    'ruta_id'      => optional(optional($b->frecuencia)->ruta)->id,
                    'ruta_nombre'  => $b->frecuencia && $b->frecuencia->ruta
                        ? (optional($b->frecuencia->ruta->origen)->nombre ?? '—') . ' → '
                          . (optional($b->frecuencia->ruta->destino)->nombre ?? '—')
                        : 'Sin ruta',
                    'venta_total'  => (float) $venta->total,
                ])
            )
            ->groupBy('ruta_id')
            ->map(fn ($g) => [
                'ruta'            => $g->first()['ruta_nombre'],
                'total_recaudado' => round($g->sum('venta_total'), 2),
                'boletos_count'   => $g->count(),
            ])
            ->values();

        $ultimasTransacciones = $ventas
            ->sortByDesc('created_at')
            ->map(fn (Venta $v) => [
                'id'       => $v->id,
                'hora'     => $v->created_at->format('H:i'),
                'total'    => (float) $v->total,
                'boletos'  => $v->boletos->count(),
                'pasajero' => optional($v->boletos->first()?->pasajero)->nombre_completo ?? '—',
                'ruta'     => $v->boletos->first()?->frecuencia?->ruta
                    ? (optional($v->boletos->first()->frecuencia->ruta->origen)->nombre ?? '—')
                      . ' → '
                      . (optional($v->boletos->first()->frecuencia->ruta->destino)->nombre ?? '—')
                    : 'Sin ruta',
            ])
            ->values();

        $pdf = Pdf::loadView('ventanilla.reporte_pdf', [
            'cajero'               => $cajero,
            'cierreExistente'      => $cierreExistente,
            'totalBruto'           => $totalBruto,
            'totalNeto'            => $totalNeto,
            'totalReembolsos'      => $totalReembolsos,
            'totalBoletos'         => $totalBoletos,
            'promedioPorBoleto'    => $promedioPorBoleto,
            'recaudacionPorRuta'   => $recaudacionPorRuta,
            'ultimasTransacciones' => $ultimasTransacciones,
            'fecha'                => $hoy,
        ])
        ->setPaper('letter', 'portrait');

        $nombre = 'cierre_turno_'
            . str_replace(' ', '_', strtolower($cajero->name))
            . '_' . $hoy . '.pdf';

        return $pdf->download($nombre);
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


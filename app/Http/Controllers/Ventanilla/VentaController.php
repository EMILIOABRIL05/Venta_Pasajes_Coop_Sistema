<?php

namespace App\Http\Controllers\Ventanilla;

use App\Exports\CierreTurnoExport;
use App\Http\Controllers\Controller;
use App\Mail\BoletoVendido;
use App\Models\Boleto;
use App\Models\CierreTurno;
use App\Models\Frecuencia;
use App\Models\Pasajero;
use App\Models\Reembolso;
use App\Models\Ruta;
use App\Models\Venta;
use App\Models\Viaje;
use App\Services\CierreTurnoService;
use App\Services\PricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class VentaController extends Controller
{
    /**
     * Constructor del controlador.
     * Inyecta el servicio de cierre de turno.
     */
    public function __construct(
        private readonly CierreTurnoService $servicioCierre,
        private readonly PricingService $pricingService,
    ) {}
    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * Muestra el listado de rutas disponibles y el mapa de asientos.
     */
    public function index()
    {
        $hoy = now()->toDateString();

        $rutas = Ruta::with(['origen', 'destino'])
            ->whereHas('frecuencias.viajes', function ($query) use ($hoy) {
                $query->where('fecha', '>=', $hoy)
                    ->whereIn('estado', ['programado', 'En Terminal']);
            })
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
        $datosValidados = $request->validate([
            'ruta_id' => ['required', 'integer', 'exists:rutas,id'],
            'cedula' => ['required', 'string', 'regex:/^\d{10}$/'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'edad' => ['required', 'integer', 'min:0', 'max:120'],
            'tiene_discapacidad' => ['nullable', 'boolean'],
            'asientos' => ['required', 'array', 'min:1', 'max:40'],
            'asientos.*' => ['required', 'integer', 'between:1,40', 'distinct'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 10 dígitos numéricos.',
            'nombre_completo.required' => 'El nombre completo del pasajero es obligatorio.',
            'edad.required' => 'La edad del pasajero es obligatoria.',
            'edad.min' => 'La edad no puede ser negativa.',
            'edad.max' => 'La edad ingresada no es válida.',
        ]);

        // ── 2. Bloqueo de ruta por viaje en curso o finalizado ────────────────
        //    Regla de negocio: si algún viaje de hoy para esta ruta está 'En Curso' o
        //    'Finalizada', se bloquea la venta de pasajes para toda la ruta.
        $hoy = now()->toDateString();

        $viajeBloqueado = Viaje::where('fecha', '<=', $hoy)
            ->whereIn('estado', ['En Curso', 'Finalizada'])
            ->whereHas('frecuencia', function ($q) use ($datosValidados) {
                $q->where('ruta_id', $datosValidados['ruta_id']);
            })
            ->exists();

        if ($viajeBloqueado) {
            return back()
                ->withInput()
                ->with('error', 'Ruta en viaje, no se pueden vender pasajes.');
        }

        // ── 3. Preparación de datos (fuera del lock transaccional) ───────────
        //    Toda operación que NO requiera acceso a la BD debe hacerse aquí,
        //    para minimizar el tiempo que los registros quedan bloqueados.
        $asientos = array_values(array_unique($datosValidados['asientos']));
        $edad = (int) $datosValidados['edad'];
        $tieneDiscapacidad = $request->boolean('tiene_discapacidad');

        // Obtener precio base de la ruta
        $ruta = Ruta::findOrFail($datosValidados['ruta_id']);
        $precioBase = (float) $ruta->precio_base;

        $frecuencia = Frecuencia::where('ruta_id', $datosValidados['ruta_id'])->first();
        $viajeActivo = Viaje::with('bus.asientos')
            ->where('fecha', '>=', $hoy)
            ->whereHas('frecuencia', function ($q) use ($datosValidados) {
                $q->where('ruta_id', $datosValidados['ruta_id']);
            })
            ->whereIn('estado', ['programado', 'En Terminal'])
            ->orderBy('fecha')
            ->first();

        // Verificar que ninguno de los asientos ya esté vendido en la base de datos para este viaje (si el viaje existe)
        if ($viajeActivo) {
            $vendidos = Boleto::where('frecuencia_id', $viajeActivo->frecuencia_id)
                ->whereIn('numero_asiento', array_map('strval', $asientos))
                ->pluck('numero_asiento');

            if ($vendidos->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('error', 'Los siguientes asientos ya han sido vendidos para este viaje: '.$vendidos->map(fn ($s) => "#{$s}")->join(', ').'.');
            }
        }

        $categoriasPorAsiento = [];
        $preciosPorAsiento = [];
        $total = 0.0;

        foreach ($asientos as $numeroAsiento) {
            $categoria = 'estandar';

            if ($viajeActivo?->bus) {
                $categoria = $viajeActivo->bus->asientos
                    ->firstWhere('numero', (int) $numeroAsiento)
                    ?->categoria ?? 'estandar';
            }

            $recargoAsiento = $categoria === 'vip'
                ? round($precioBase * 0.5, 2)
                : 0.0;

            $precioAsiento = $this->pricingService->calcularPrecioFinal(
                $precioBase,
                $recargoAsiento,
                $edad,
                $tieneDiscapacidad,
            );

            $categoriasPorAsiento[(string) $numeroAsiento] = $categoria;
            $preciosPorAsiento[(string) $numeroAsiento] = $precioAsiento;
            $total += $precioAsiento;
        }

        // ── 2.5 Verificación de disponibilidad en tiempo real ─────────────────
        //    Doble capa de protección contra ventas simultáneas del mismo asiento:
        //      a) Cache::lock()  → bloqueo atómico (evita race conditions)
        //      b) Query 60s      → red de seguridad contra boletos recién creados
        [$conflicto, $bloqueos] = $this->verifyAsientosDisponibles($asientos);

        if ($conflicto !== null) {
            return back()
                ->withInput()
                ->with('error', $conflicto);
        }

        // ── 3. Transacción atómica con reintentos ante deadlock ───────────────
        try {
            $venta = DB::transaction(function () use ($datosValidados, $asientos, $total, $frecuencia, $viajeActivo, $categoriasPorAsiento, $preciosPorAsiento) {

                // 3a. Resolver, crear o restaurar pasajero
                $pasajero = Pasajero::withTrashed()->where('cedula', $datosValidados['cedula'])->first();
                if (! $pasajero) {
                    $pasajero = Pasajero::create([
                        'cedula' => $datosValidados['cedula'],
                        'nombre_completo' => $datosValidados['nombre_completo'],
                        'edad' => $datosValidados['edad'],
                    ]);
                } else {
                    if ($pasajero->trashed()) {
                        $pasajero->restore();
                    }
                    $pasajero->update([
                        'nombre_completo' => $datosValidados['nombre_completo'],
                        'edad' => $datosValidados['edad'],
                    ]);
                }

                // 3b. Cabecera de la venta ─────────────────────────────────────
                //     FK: ventas.user_id → users.id (cajero autenticado)
                $venta = Venta::create([
                    'user_id' => auth()->id(),
                    'total' => $total,
                    'canal_venta' => Venta::CANAL_VENTANILLA,
                ]);

                // 3c. Preparación de datos de boletos
                $datosBoletos = array_map(fn (int $asiento) => [
                    'pasajero_id' => $pasajero->id,  // FK → pasajeros.id ✓
                    'frecuencia_id' => $frecuencia ? $frecuencia->id : null,
                    'viaje_id' => $viajeActivo?->id,
                    'numero_asiento' => (string) $asiento,
                    'categoria_asiento' => $categoriasPorAsiento[(string) $asiento] ?? 'estandar',
                    'precio_final' => $preciosPorAsiento[(string) $asiento] ?? 0,
                    'estado' => Boleto::ESTADO_ACTIVO,
                ], $asientos);

                // 3d. Inserción masiva de boletos ──────────────────────────────
                //     createMany() itera el payload y llama create() por cada
                //     elemento, disparando Boleto::booted() → UUID automático.
                $venta->boletos()->createMany($datosBoletos);

                // Devolver el modelo hidratado (sin query adicional al cliente)
                return $venta->load('boletos');

            }, 2); // ← 2 reintentos automáticos ante deadlock de InnoDB

        } catch (QueryException $excepcion) {
            $estadoSql = $excepcion->getCode();

            $contextoLog = [
                'sqlstate' => $estadoSql,
                'sql' => $excepcion->getSql(),
                'bindings' => $excepcion->getBindings(),
                'message' => $excepcion->getMessage(),
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name ?? 'N/A',
                    'email' => auth()->user()->email ?? 'N/A',
                ],
                'request' => [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'payload' => [
                        'ruta_id' => $datosValidados['ruta_id'] ?? null,
                        'cedula' => $datosValidados['cedula'] ?? null,
                        'nombre_completo' => $datosValidados['nombre_completo'] ?? null,
                        'edad' => $datosValidados['edad'] ?? null,
                        'asientos' => $asientos ?? [],
                        'total' => $total ?? 0,
                    ],
                ],
            ];

            if ($estadoSql === '23000') {
                Log::error('[Ventanilla] store() — Integrity constraint violation', $contextoLog);

                return back()
                    ->withInput()
                    ->with('error', 'Error de integridad en base de datos. Uno de los datos ingresados viola una restricción del sistema (llave foránea o valor duplicado). Verifique los datos del pasajero y la ruta seleccionada.');
            } elseif ($estadoSql === '40001') {
                Log::warning('[Ventanilla] store() — Deadlock tras reintentos', $contextoLog);

                return back()
                    ->withInput()
                    ->with('error', 'Conflicto de concurrencia. Otro cajero procesó una venta al mismo tiempo. Espere unos segundos e intente de nuevo.');
            } else {
                Log::error('[Ventanilla] store() — QueryException genérica', $contextoLog);

                return back()
                    ->withInput()
                    ->with('error', 'Ocurrió un problema al guardar la venta en la base de datos. Intente nuevamente o contacte al soporte técnico.');
            }

        } catch (Throwable $excepcion) {
            Log::critical('[Ventanilla] store() — Fallo crítico inesperado', [
                'message' => $excepcion->getMessage(),
                'file' => $excepcion->getFile().':'.$excepcion->getLine(),
                'trace' => $excepcion->getTraceAsString(),
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name ?? 'N/A',
                    'email' => auth()->user()->email ?? 'N/A',
                ],
                'request' => [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'payload' => [
                        'asientos' => $asientos ?? [],
                        'total' => $total ?? 0,
                    ],
                ],
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error inesperado del sistema. La operación fue cancelada de forma segura. Contacte al administrador e indíquele la hora exacta: '.now()->format('H:i:s d/m/Y').'.');

        } finally {
            // ── Siempre liberar los Cache locks ──────────────────────────────
            //    El bloque finally se ejecuta tanto si la transacción fue exitosa
            //    como si lanzó cualquier excepción, garantizando que los asientos
            //    queden disponibles para el siguiente intento.
            if (isset($bloqueos)) {
                collect($bloqueos)->each(fn ($bloqueo) => $bloqueo->release());
            }
        }

        // ── 3.5 Enviar correos de confirmación en segundo plano ───────────────
        $venta->load('boletos.pasajero');
        foreach ($venta->boletos as $boleto) {
            if (! empty($boleto->pasajero->correo)) {
                Mail::to($boleto->pasajero->correo)
                    ->queue(new BoletoVendido($boleto));
            }
        }

        // ── 4. Respuesta de éxito: Flash estructurado ─────────────────────────
        //    Array en sesión en lugar de string para que la vista construya
        //    un resumen visual rico con todos los detalles de la operación.
        return redirect()
            ->route('ventanilla.ventas.index')
            ->with('venta_exitosa', [
                'id' => $venta->id,
                'total' => number_format($total, 2),
                'boletos' => $venta->boletos->count(),
                'asientos' => $venta->boletos
                    ->pluck('numero_asiento')
                    ->sort()->values()->join(', '),
                'codigos' => $venta->boletos
                    ->pluck('codigo_reserva')
                    ->join(' · '),
                'boleto_ids' => $venta->boletos->pluck('id')->toArray(),
                'cajero' => auth()->user()->name ?? 'Sistema',
                'fecha' => now()->format('d/m/Y'),
                'hora' => now()->format('H:i:s'),
            ]);
    }

    // ─── Métodos pendientes de implementar ────────────────────────────────────

    /**
     * Muestra el formulario para crear una nueva venta de boletos.
     * Carga las rutas disponibles para el selector.
     *
     * @return View
     */
    public function create()
    {
        $hoy = now()->toDateString();

        $rutas = Ruta::with(['origen', 'destino'])
            ->whereHas('frecuencias.viajes', function ($query) use ($hoy) {
                $query->where('fecha', '>=', $hoy)
                    ->whereIn('estado', ['programado', 'En Terminal']);
            })
            ->orderBy('precio_base')
            ->get();

        return view('ventanilla.ventas.create', compact('rutas'));
    }

    /**
     * Obtiene los asientos ocupados y categorías para una ruta específica en el día actual (AJAX).
     *
     * @param  int  $ruta_id
     * @return JsonResponse
     */
    public function asientosPorRuta($ruta_id)
    {
        $hoy = now()->toDateString();

        $viaje = Viaje::where('fecha', '>=', $hoy)
            ->whereHas('frecuencia', function ($q) use ($ruta_id) {
                $q->where('ruta_id', $ruta_id);
            })
            ->whereIn('estado', ['programado', 'En Terminal'])
            ->with(['bus.asientos', 'frecuencia.ruta'])
            ->orderBy('fecha')
            ->first();

        if (! $viaje) {
            return response()->json([
                'success' => false,
                'message' => 'No hay viajes programados para esta ruta.',
            ]);
        }

        $bus = $viaje->bus;
        if (! $bus) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un bus asignado al viaje de esta ruta.',
            ]);
        }

        $numeroAsientos = $bus->numero_asientos;

        $seatCategories = $bus->asientos()
            ->pluck('categoria', 'numero')
            ->mapWithKeys(function ($categoria, $numero) {
                return [(string) $numero => $categoria];
            })
            ->all();

        $occupiedSeats = Boleto::where('frecuencia_id', $viaje->frecuencia_id)
            ->pluck('numero_asiento')
            ->map(fn ($num) => (string) $num)
            ->toArray();

        return response()->json([
            'success' => true,
            'viaje_id' => $viaje->id,
            'viaje_fecha' => $viaje->fecha->format('d/m/Y'),
            'numero_asientos' => $numeroAsientos,
            'seatCategories' => $seatCategories,
            'occupiedSeats' => $occupiedSeats,
            'precio_base' => (float) ($viaje->frecuencia->ruta->precio_base ?? 0),
        ]);
    }

    /**
     * Muestra los detalles de una venta específica y sus boletos.
     *
     * @return View
     */
    public function show(Venta $venta)
    {
        if ($venta->user_id !== auth()->id()) {
            abort(403, 'Acceso denegado');
        }

        $venta->load('boletos.pasajero');

        return view('ventanilla.ventas.show', compact('venta'));
    }

    /**
     * Anula un boleto específico, cambiando su estado a 'Anulado',
     * registrando la fecha de cancelación y liberando el asiento.
     * Usa DB::transaction para garantizar la integridad.
     *
     * @param  string  $id  UUID del boleto
     * @return RedirectResponse
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

                // 4. Crear un reembolso automático aprobado para cuadrar el cierre de caja
                Reembolso::create([
                    'venta_id' => $boleto->venta_id,
                    'monto' => $boleto->precio_final,
                    'motivo' => 'Anulación directa en ventanilla (dentro de 30 min)',
                    'estado' => 'aprobado',
                    'fecha_solicitud' => now(),
                    'fecha_resolucion' => now(),
                ]);

                // 5. Registrar log de cambios
                Log::info('[Ventanilla] Boleto anulado y asiento liberado.', [
                    'boleto_id' => $boleto->id,
                    'numero_asiento' => $boleto->numero_asiento,
                    'venta_id' => $boleto->venta_id,
                    'usuario_id' => auth()->id(),
                ]);
            });

            return back()->with('success', 'Boleto anulado correctamente. El asiento ha sido liberado.');
        } catch (\Exception $e) {
            Log::error('[Ventanilla] Error al anular boleto', [
                'boleto_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
        $hoy = now()->toDateString();
        $idUsuario = auth()->id();

        $resumen = $this->servicioCierre->resumenCompleto($idUsuario, $hoy);

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
        $idUsuario = auth()->id();
        $hoy = now()->toDateString();

        // ── 1. Guardia contra duplicado ───────────────────────────────────────
        if (CierreTurno::existeParaHoy($idUsuario, $hoy)) {
            return back()->with(
                'error',
                'Error: Ya has registrado un cierre de caja para el turno de hoy.'
            );
        }

        // ── 2. Obtener totales del servicio (sin lógica duplicada) ────────────
        $resumen = $this->servicioCierre->resumenCompleto($idUsuario, $hoy);

        // ── 3. Persistencia atómica ───────────────────────────────────────────
        try {
            DB::transaction(function () use ($idUsuario, $hoy, $resumen) {
                // Segunda verificación dentro de la transacción para evitar
                // condición de carrera si dos requests llegan casi simultáneamente.
                if (CierreTurno::existeParaHoy($idUsuario, $hoy)) {
                    throw new \RuntimeException('duplicate_cierre');
                }

                CierreTurno::create([
                    'user_id' => $idUsuario,
                    'fecha' => $hoy,
                    'total_bruto' => $resumen['totalBruto'],
                    'total_reembolsos' => $resumen['totalReembolsos'],
                    'total_neto' => $resumen['totalNeto'],
                    'total_boletos' => $resumen['totalBoletos'],
                ]);
            });

        } catch (\RuntimeException $excepcion) {
            if ($excepcion->getMessage() === 'duplicate_cierre') {
                return back()->with(
                    'error',
                    'Error: Ya has registrado un cierre de caja para el turno de hoy.'
                );
            }
            Log::error('[Ventanilla] storeCierre() — RuntimeException inesperada', [
                'message' => $excepcion->getMessage(),
                'user_id' => $idUsuario,
                'fecha' => $hoy,
            ]);

            return back()->with('error', 'Ocurrió un error inesperado. La operación fue cancelada de forma segura.');

        } catch (Throwable $excepcion) {
            Log::critical('[Ventanilla] storeCierre() — Fallo crítico', [
                'message' => $excepcion->getMessage(),
                'file' => $excepcion->getFile().':'.$excepcion->getLine(),
                'user_id' => $idUsuario,
                'fecha' => $hoy,
            ]);

            return back()->with(
                'error',
                'Error al guardar el cierre. Contacte al administrador e indique la hora: '.now()->format('H:i:s d/m/Y').'.'
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
     *
     * @return Response
     */
    public function reportePdf()
    {
        $cajero = auth()->user();
        $hoy = now()->toDateString();
        $resumen = $this->servicioCierre->resumenCompleto($cajero->id, $hoy);

        $pdf = Pdf::loadView(
            'ventanilla.reporte_pdf',
            array_merge($resumen, ['cajero' => $cajero, 'fecha' => $hoy])
        )->setPaper('letter', 'portrait');

        return $pdf->download(
            'cierre_turno_'.str_replace(' ', '_', strtolower($cajero->name))."_{$hoy}.pdf"
        );
    }

    /**
     * Exporta el cierre del turno a Excel (.xlsx) con dos hojas.
     * CierreTurnoExport reutiliza CierreTurnoService internamente.
     */
    public function exportarExcel()
    {
        $cajero = auth()->user();
        $hoy = now()->toDateString();

        return Excel::download(
            new CierreTurnoExport($cajero->id, $hoy, $cajero->name),
            'cierre_turno_'.str_replace(' ', '_', strtolower($cajero->name))."_{$hoy}.xlsx"
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
     * @return array{0: string|null, 1: array} [mensaje_error|null, locks_adquiridos]
     */
    private function verifyAsientosDisponibles(array $asientos): array
    {
        $TIEMPO_BLOQUEO = 60; // segundos
        $VENTANA_SEGUNDOS = 60; // ventana de detección en BD

        // ── Capa 1: Cache locks atómicos ──────────────────────────────────────
        $bloqueos = [];
        $bloqueados = [];

        foreach ($asientos as $asiento) {
            $bloqueo = Cache::lock("asiento:{$asiento}", $TIEMPO_BLOQUEO);

            if ($bloqueo->get()) {
                $bloqueos[] = $bloqueo;          // Adquirido: guardar para liberar luego
            } else {
                $bloqueados[] = $asiento;     // Otro cajero lo está procesando ahora
            }
        }

        if (! empty($bloqueados)) {
            // Liberar los locks que SÍ adquirimos antes de abortar
            collect($bloqueos)->each(fn ($l) => $l->release());

            $lista = implode(', ', array_map(fn ($s) => "#{$s}", $bloqueados));

            return [
                "Los asientos {$lista} están siendo procesados por otro cajero. Intente en {$TIEMPO_BLOQUEO} segundos.",
                [],
            ];
        }

        // ── Capa 2: Ventana de 60s en BD ──────────────────────────────────────
        //    Detecta boletos creados recientemente aunque el lock ya se liberó
        //    (p.ej. venta exitosa hace 30s → lock liberado → asiento bloqueado aún).
        $vendidosRecientemente = Boleto::whereIn(
            'numero_asiento',
            array_map('strval', $asientos)
        )
            ->where('created_at', '>=', now()->subSeconds($VENTANA_SEGUNDOS))
            ->pluck('numero_asiento');

        if ($vendidosRecientemente->isNotEmpty()) {
            collect($bloqueos)->each(fn ($l) => $l->release());

            $lista = $vendidosRecientemente->map(fn ($s) => "#{$s}")->join(', ');

            return [
                "Los asientos {$lista} ya fueron vendidos en los últimos {$VENTANA_SEGUNDOS} segundos. Seleccione otros asientos.",
                [],
            ];
        }

        // Sin conflictos: devolver locks para que el llamador los libere en finally
        return [null, $bloqueos];
    }
}

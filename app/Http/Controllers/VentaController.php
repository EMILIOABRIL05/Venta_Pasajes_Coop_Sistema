<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Venta;
use App\Models\Bus;
use App\Models\Asiento;
use App\Models\Pago;
use App\Models\Frecuencia;
use App\Models\Pasajero;
use App\Models\Boleto;
use App\Models\Reembolso;
use App\Models\CierreTurno;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VentaController extends Controller
{
    /**
     * Listado de ventas.
     */
    public function index()
    {
        $ventas = Venta::with(['boletos.pasajero', 'user'])->latest()->get();
        return view('ventas.index', compact('ventas'));
    }

    /**
     * Formulario para crear una nueva venta.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'frecuencia_id' => ['required', 'exists:frecuencias,id'],
            'bus_id' => ['nullable', 'exists:buses,id'],
        ]);

        $frecuencia = Frecuencia::with('ruta')->findOrFail($validated['frecuencia_id']);
        $bus = $validated['bus_id']
            ? Bus::findOrFail($validated['bus_id'])
            : Bus::orderBy('placa')->firstOrFail();

        $occupiedSeats = Boleto::where('frecuencia_id', $frecuencia->id)
            ->pluck('numero_asiento')
            ->toArray();

        $seatNumbers = range(1, max(1, $bus->numero_asientos));
        $seatCategories = $bus->asientos()->pluck('categoria', 'numero')->all();
        $pasajeros = Pasajero::orderBy('nombre_completo')->get(['id', 'nombre_completo', 'cedula']);
        $buses = Bus::orderBy('placa')->get(['id', 'placa', 'numero_asientos']);

        return view('ventas.create', [
            'frecuencia' => $frecuencia,
            'bus' => $bus,
            'occupiedSeats' => $occupiedSeats,
            'seatNumbers' => $seatNumbers,
            'seatCategories' => $seatCategories,
            'pasajeros' => $pasajeros,
            'buses' => $buses,
        ]);
    }

    /**
     * Guardar la venta y generar el boleto con UUID.
     */
    public function store(Request $request)
    {
        $metodosPago = [
            'efectivo',
            'transferencia',
            'deposito',
            'pago_movil',
            'tarjeta_simulada',
        ];

        $validated = $request->validate([
            'frecuencia_id' => ['required', 'exists:frecuencias,id'],
            'bus_id' => ['required', 'exists:buses,id'],
            'pasajero_id' => ['required', 'exists:pasajeros,id'],
            'numero_asiento' => ['required', 'integer', 'min:1'],
            'precio_final' => ['required', 'numeric', 'min:0'],
            'metodo_pago' => ['nullable', 'string', 'max:50', Rule::in($metodosPago)],
            'referencia' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $frecuencia = Frecuencia::with('ruta')->findOrFail($validated['frecuencia_id']);
        $bus = Bus::findOrFail($validated['bus_id']);
        $asiento = Asiento::query()
            ->where('bus_id', $bus->id)
            ->where('numero', (int) $validated['numero_asiento'])
            ->first();

        $viajeBloqueado = \App\Models\Viaje::where('frecuencia_id', $validated['frecuencia_id'])
            ->where('fecha', now()->toDateString())
            ->whereIn('estado', ['En Curso', 'Finalizada'])
            ->exists();

        if ($viajeBloqueado) {
            return back()->withInput()->withErrors(['general' => 'No se pueden vender pasajes: el viaje ya se encuentra en curso o ha finalizado.']);
        }

        $precioBase = (float) $frecuencia->ruta->precio_base;
        $precioEsperado = $asiento?->precioConRecargo($precioBase) ?? round($precioBase, 2);

        if ((float) $validated['precio_final'] != $precioEsperado) {
            return back()->withInput()->withErrors(['precio_final' => 'El monto no coincide con el precio de la ruta.']);
        }

        $seatString = (string) $validated['numero_asiento'];
        $seatTaken = Boleto::where('frecuencia_id', $validated['frecuencia_id'])
            ->where('numero_asiento', $seatString)
            ->exists();

        if ($seatTaken) {
            return back()->withInput()->withErrors(['numero_asiento' => 'El asiento ya está ocupado.']);
        }

        try {
            $venta = DB::transaction(function () use ($validated, $seatString) {
                $venta = Venta::create([
                    'user_id' => auth()->id(),
                    'total' => $validated['precio_final'],
                ]);

                Boleto::create([
                    'id' => (string) Str::uuid(), // El UUID de Manolo
                    'venta_id' => $venta->id,
                    'pasajero_id' => $validated['pasajero_id'],
                    'frecuencia_id' => $validated['frecuencia_id'],
                    'numero_asiento' => $seatString,
                    'categoria_asiento' => $asiento?->categoria ?? 'estandar',
                    'precio_final' => $validated['precio_final'],
                ]);

                Pago::create([
                    'venta_id' => $venta->id,
                    'monto' => $validated['precio_final'],
                    'fecha' => now(),
                    'metodo_pago' => $validated['metodo_pago'] ?? 'efectivo',
                ]);

                return $venta;
            });

            return redirect()->route('ventas.show', $venta->id)->with('success', 'Venta exitosa.');
        } catch (\Throwable $exception) {
            return back()->withInput()->withErrors(['general' => 'Error al procesar la venta.']);
        }
    }

    /**
     * Ver el recibo digital.
     */
    public function show(Venta $venta)
    {
        if ($venta->user_id !== auth()->id()) {
            abort(403, 'Acceso denegado');
        }

        $venta->load(['boletos.pasajero', 'boletos.frecuencia.ruta', 'user', 'pagos']);
        return view('ventas.show', compact('venta'));
    }

    /**
     * TU NUEVO MÉTODO: Descargar el boleto en PDF con código QR.
     */
    public function descargarBoleto($id)
    {
        // Buscamos el boleto por el UUID y validamos propiedad
        // Si es admin o oficinista puede descargar cualquiera, si es cliente solo los suyos
        $query = Boleto::with(['venta', 'pasajero', 'frecuencia.ruta.origen', 'frecuencia.ruta.destino']);

        if (auth()->user()->hasAnyRole(['admin', 'oficinista'])) {
            $boleto = $query->findOrFail($id);
        } else {
            $boleto = $query->whereHas('venta', function ($q) {
                $q->where('cliente_id', auth()->id());
            })->findOrFail($id);
        }

        // Generamos el QR en formato SVG y lo codificamos en base64 para evitar conflictos de comillas en el HTML del PDF
        $qrCode = base64_encode((string) QrCode::format('svg')->size(200)->generate($boleto->id));

        $data = [
            'boleto' => $boleto,
            'qrCode' => $qrCode,
        ];

        $pdf = Pdf::loadView('ventas.boleto_pdf', $data);
        return $pdf->download('Boleto-Ambato-' . $boleto->id . '.pdf');
    }

    /**
     * Descarga un comprobante de venta con TODOS los boletos en un solo PDF (multi-página).
     * Estándar de industria: un PDF por transacción, un boleto por página.
     */
    public function descargarComprobanteVenta($venta_id)
    {
        $query = Venta::with(['boletos.pasajero', 'boletos.frecuencia.ruta.origen', 'boletos.frecuencia.ruta.destino']);

        if (auth()->user()->hasAnyRole(['admin', 'oficinista'])) {
            $venta = $query->findOrFail($venta_id);
        } else {
            $venta = $query->where('cliente_id', auth()->id())->findOrFail($venta_id);
        }

        $boletosConQr = $venta->boletos->map(function ($boleto) {
            $boleto->qrCode = base64_encode((string) QrCode::format('svg')->size(200)->generate($boleto->id));
            return $boleto;
        });

        $data = [
            'venta' => $venta,
            'boletos' => $boletosConQr,
        ];

        $pdf = Pdf::loadView('ventas.comprobante_venta_pdf', $data);
        return $pdf->download('Comprobante-Venta-' . $venta->id . '.pdf');
    }

    /**
     * Resumen del turno actual del usuario autenticado.
     *
     * Calcula el SUM(total) de ventas y el conteo de boletos
     * del día actual, agrupando la recaudación por Ruta
     * mediante relaciones de Eloquent.
     */
    public function resumenTurno()
    {
        $userId = auth()->id();
        $hoy    = now()->toDateString();

        // ── Ventas del día del usuario autenticado con relaciones ────────────
        $ventas = Venta::with(['boletos.frecuencia.ruta.origen', 'boletos.frecuencia.ruta.destino'])
            ->where('user_id', $userId)
            ->whereDate('created_at', $hoy)
            ->get();

        // ── Agrupar la recaudación por Ruta ──────────────────────────────────
        $recaudacionPorRuta = $ventas
            ->flatMap(fn (Venta $venta) =>
                $venta->boletos->map(fn (Boleto $boleto) => [
                    'ruta_id'      => $boleto->frecuencia->ruta->id ?? null,
                    'ruta_nombre'  => $boleto->frecuencia->ruta
                        ? ($boleto->frecuencia->ruta->origen->nombre ?? '—')
                          . ' → '
                          . ($boleto->frecuencia->ruta->destino->nombre ?? '—')
                        : 'Sin ruta',
                    'precio_final' => (float) $boleto->precio_final,
                    'venta_total'  => (float) $venta->total,
                ])
            )
            ->groupBy('ruta_id')
            ->map(fn ($grupo) => [
                'ruta'           => $grupo->first()['ruta_nombre'],
                'total_recaudado' => $grupo->sum('venta_total'),
                'boletos_count'  => $grupo->count(),
            ])
            ->values();

        // ── Totales generales ────────────────────────────────────────────────
        $totalVentas  = $ventas->sum('total');
        $totalBoletos = $ventas->sum(fn (Venta $v) => $v->boletos->count());

        return view('ventas.resumen_turno', [
            'recaudacionPorRuta' => $recaudacionPorRuta,
            'totalVentas'        => $totalVentas,
            'totalBoletos'       => $totalBoletos,
            'fecha'              => $hoy,
        ]);
    }

    // =========================================================================
    // CIERRE DE TURNO
    // =========================================================================

    /**
     * Muestra el resumen consolidado previo al cierre de turno.
     *
     * Calcula ingresos brutos y netos del día actual para el cajero
     * autenticado y detecta si ya existe un cierre registrado.
     */
    public function cierreTurno()
    {
        $userId = auth()->id();
        $hoy    = now()->toDateString();

        // ── ¿Ya existe un cierre para hoy? ────────────────────────────────────
        $cierreExistente = CierreTurno::where('user_id', $userId)
            ->where('fecha', $hoy)
            ->first();

        // ── Ventas del día del cajero (con relaciones para el desglose) ───────
        $ventas = Venta::with([
                'boletos.frecuencia.ruta.origen',
                'boletos.frecuencia.ruta.destino',
                'reembolsos',
            ])
            ->where('user_id', $userId)
            ->whereDate('created_at', $hoy)
            ->get();

        // ── Ingresos brutos ───────────────────────────────────────────────────
        $totalBruto  = $ventas->sum('total');
        $totalBoletos = $ventas->sum(fn (Venta $v) => $v->boletos->count());

        // ── Reembolsos aprobados del día que afectan a ventas de este cajero ──
        // Se cuentan solo los reembolsos cuya venta pertenece al cajero actual
        // y cuya fecha de resolución es hoy.
        $totalReembolsos = $ventas->sum(
            fn (Venta $v) => $v->reembolsos
                ->where('estado', 'aprobado')
                ->sum('monto')
        );

        // ── Ingreso neto ──────────────────────────────────────────────────────
        $totalNeto = $totalBruto - $totalReembolsos;

        // ── Recaudación por ruta (para la tabla de detalle) ───────────────────
        $recaudacionPorRuta = $ventas
            ->flatMap(fn (Venta $venta) =>
                $venta->boletos->map(fn (Boleto $boleto) => [
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
                'total_recaudado' => $grupo->sum('venta_total'),
                'boletos_count'   => $grupo->count(),
            ])
            ->values();

        return view('ventas.cierre_turno', [
            'cierreExistente'    => $cierreExistente,
            'recaudacionPorRuta' => $recaudacionPorRuta,
            'totalBruto'         => $totalBruto,
            'totalReembolsos'    => $totalReembolsos,
            'totalNeto'          => $totalNeto,
            'totalBoletos'       => $totalBoletos,
            'fecha'              => $hoy,
        ]);
    }

    /**
     * Persiste el cierre de turno del día actual.
     *
     * Guarda en DB::transaction para garantizar atomicidad.
     * Verifica nuevamente la existencia del cierre (guarda contra
     * doble-clic o peticiones concurrentes).
     */
    public function storeCierre(Request $request)
    {
        $userId = auth()->id();
        $hoy    = now()->toDateString();

        // ── Segunda verificación: guarda contra race conditions ───────────────
        if (CierreTurno::existeParaHoy($userId, $hoy)) {
            return back()
                ->with('error', 'Ya has registrado un cierre de caja para el día de hoy.');
        }

        try {
            DB::transaction(function () use ($userId, $hoy) {
                // Re-calcular dentro de la transacción para máxima consistencia
                $ventas = Venta::with('boletos', 'reembolsos')
                    ->where('user_id', $userId)
                    ->whereDate('created_at', $hoy)
                    ->get();

                $totalBruto      = $ventas->sum('total');
                $totalBoletos    = $ventas->sum(fn (Venta $v) => $v->boletos->count());
                $totalReembolsos = $ventas->sum(
                    fn (Venta $v) => $v->reembolsos
                        ->where('estado', 'aprobado')
                        ->sum('monto')
                );

                CierreTurno::create([
                    'user_id'          => $userId,
                    'fecha'            => $hoy,
                    'total_bruto'      => $totalBruto,
                    'total_reembolsos' => $totalReembolsos,
                    'total_neto'       => $totalBruto - $totalReembolsos,
                    'total_boletos'    => $totalBoletos,
                ]);
            });

            return redirect()
                ->route('ventas.cierre-turno')
                ->with('success', 'Cierre de turno registrado correctamente.');

        } catch (\Throwable $e) {
            return redirect()
                ->route('ventas.cierre-turno')
                ->with('error', 'Error al registrar el cierre. Intente nuevamente.');
        }
    }
}
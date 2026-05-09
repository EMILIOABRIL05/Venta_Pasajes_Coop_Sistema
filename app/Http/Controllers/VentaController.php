<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Venta;
use App\Models\Bus;
use App\Models\Pago;
use App\Models\Frecuencia;
use App\Models\Pasajero;
use App\Models\Boleto;
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
        $pasajeros = Pasajero::orderBy('nombre_completo')->get(['id', 'nombre_completo', 'cedula']);
        $buses = Bus::orderBy('placa')->get(['id', 'placa', 'numero_asientos']);

        return view('ventas.create', [
            'frecuencia' => $frecuencia,
            'bus' => $bus,
            'occupiedSeats' => $occupiedSeats,
            'seatNumbers' => $seatNumbers,
            'pasajeros' => $pasajeros,
            'buses' => $buses,
        ]);
    }

    /**
     * Guardar la venta y generar el boleto con UUID.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'frecuencia_id' => ['required', 'exists:frecuencias,id'],
            'bus_id' => ['required', 'exists:buses,id'],
            'pasajero_id' => ['required', 'exists:pasajeros,id'],
            'numero_asiento' => ['required', 'integer', 'min:1'],
            'precio_final' => ['required', 'numeric', 'min:0'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $frecuencia = Frecuencia::with('ruta')->findOrFail($validated['frecuencia_id']);
        $bus = Bus::findOrFail($validated['bus_id']);

        if ($validated['precio_final'] != $frecuencia->ruta->precio_base) {
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
        $venta->load(['boletos.pasajero', 'boletos.frecuencia.ruta', 'user', 'pagos']);
        return view('ventas.show', compact('venta'));
    }

    /**
     * TU NUEVO MÉTODO: Descargar el boleto en PDF con código QR.
     */
    public function descargarBoleto($id)
    {
        // Buscamos el boleto por el UUID
        $boleto = Boleto::with(['venta', 'pasajero', 'frecuencia.ruta'])->findOrFail($id);

        // Generamos el QR con el UUID contenido en $boleto->id
        $qrCode = QrCode::size(200)->generate($boleto->id);

        $data = [
            'boleto' => $boleto,
            'qrCode' => $qrCode,
        ];

        $pdf = Pdf::loadView('ventas.boleto_pdf', $data);
        return $pdf->download('boleto_' . $boleto->pasajero->cedula . '.pdf');
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
}
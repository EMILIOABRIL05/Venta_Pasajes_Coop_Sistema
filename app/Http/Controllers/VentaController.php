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

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
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
     * Store a newly created resource in storage.
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

        $bus = Bus::findOrFail($validated['bus_id']);

        if ($validated['numero_asiento'] > $bus->numero_asientos) {
            return back()
                ->withInput()
                ->withErrors(['numero_asiento' => 'El asiento seleccionado está fuera del rango del bus.']);
        }

        $seatString = (string) $validated['numero_asiento'];
        $seatTaken = Boleto::where('frecuencia_id', $validated['frecuencia_id'])
            ->where('numero_asiento', $seatString)
            ->exists();

        if ($seatTaken) {
            return back()
                ->withInput()
                ->withErrors(['numero_asiento' => 'El asiento seleccionado ya está ocupado para esta frecuencia.']);
        }

        try {
            $venta = DB::transaction(function () use ($validated, $seatString) {
                $venta = Venta::create([
                    'user_id' => auth()->id(),
                    'total' => $validated['precio_final'],
                ]);

                Boleto::create([
                    'id' => (string) Str::uuid(),
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
                    'referencia' => $validated['referencia'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? 'Pago registrado automáticamente al confirmar la venta.',
                ]);

                return $venta;
            });

            return redirect()->route('ventas.show', $venta->id)
                ->with('success', 'Venta completada exitosamente.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['general' => 'No se pudo completar la venta. Intente nuevamente.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'boletos.pasajero',
            'boletos.frecuencia.ruta.origen',
            'boletos.frecuencia.ruta.destino',
            'user',
            'pagos'
        ]);

        return view('ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

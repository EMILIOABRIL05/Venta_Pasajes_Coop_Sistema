<?php

namespace App\Livewire\Web;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Viaje;
use App\Models\Pasajero;
use App\Models\Venta;
use App\Models\Boleto;
use App\Traits\CalculaDescuentoPorEdad; // Trait de Manuel
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CarritoCompra extends Component
{
    use CalculaDescuentoPorEdad;

    // Propiedades del Sprint 3
    public $viajeId;
    public $viaje;
    public $asientosSeleccionados = [];
    public $datosPasajeros = []; // Nombre, Cédula, Edad
    public $total = 0;

    protected $rules = [
        'datosPasajeros.*.nombre' => 'required|string|min:3',
        'datosPasajeros.*.cedula' => 'required|string|digits:10',
        'datosPasajeros.*.edad' => 'required|integer|min:0|max:110',
    ];

    public function mount($viajeId)
    {
        $this->viajeId = $viajeId;
        // Cargamos el viaje con su frecuencia y ruta para obtener el precio base
        $this->viaje = Viaje::with(['frecuencia.ruta'])->findOrFail($viajeId);
    }

    // Se activa cuando seleccionas un asiento en el x-seat-map
public function seleccionarAsiento($numeroAsiento)
{
    $numeroAsiento = (string) $numeroAsiento;

    if (in_array($numeroAsiento, $this->asientosSeleccionados)) {
        $this->asientosSeleccionados = array_diff($this->asientosSeleccionados, [$numeroAsiento]);
        unset($this->datosPasajeros[$numeroAsiento]);
    } else {
        $this->asientosSeleccionados[] = $numeroAsiento;
        $this->datosPasajeros[$numeroAsiento] = [
            'nombre' => '',
            'cedula' => '',
            'edad' => '',
            'precio' => $this->viaje->frecuencia->ruta->precio_base
        ];
    }
    
    $this->calcularTotal();
}

    public function calcularTotal()
    {
        $this->total = 0;
        foreach ($this->datosPasajeros as $key => $pasajero) {
            if ($pasajero['edad'] !== '') {
                // Usamos el Trait de Manuel para el descuento del 50%
                $precioFinal = $this->obtenerPrecioConDescuento(
                    $this->viaje->frecuencia->ruta->precio_base, 
                    $pasajero['edad']
                );
                $this->datosPasajeros[$key]['precio'] = $precioFinal;
                $this->total += $precioFinal;
            } else {
                $this->total += $this->viaje->frecuencia->ruta->precio_base;
            }
        }
    }

    public function confirmarVenta()
    {
        $this->validate();

        if (empty($this->asientosSeleccionados)) {
            session()->flash('error', 'Debes seleccionar al menos un asiento.');
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Crear la Venta
            $venta = Venta::create([
                'user_id' => Auth::id(), // Puede ser null si se permite compra de invitados, pero la ruta tiene middleware auth
                'total' => $this->total,
            ]);

            // 2. Registrar/Actualizar Pasajeros y 3. Crear Boletos
            foreach ($this->asientosSeleccionados as $asiento) {
                $datos = $this->datosPasajeros[$asiento];

                $pasajero = Pasajero::updateOrCreate(
                    ['cedula' => $datos['cedula']],
                    [
                        'nombre_completo' => $datos['nombre'],
                        'edad' => $datos['edad'],
                    ]
                );

                Boleto::create([
                    'venta_id' => $venta->id,
                    'pasajero_id' => $pasajero->id,
                    // Nota: el sistema actual enlaza el boleto a la frecuencia (según la BD actual)
                    'frecuencia_id' => $this->viaje->frecuencia_id,
                    'numero_asiento' => $asiento,
                    'precio_final' => $datos['precio'],
                ]);
            }

            DB::commit();

            // Limpiar selección
            $this->asientosSeleccionados = [];
            $this->datosPasajeros = [];
            $this->total = 0;

            session()->flash('success', '¡Compra realizada con éxito! Tus boletos han sido generados.');
            
            // Redirigir a alguna ruta de confirmación o dashboard
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al procesar la compra: ' . $e->getMessage());
        }
    }
    public function updated($propertyName)
{
    // Si cambia cualquier edad en el formulario, recalculamos el total
    if (str_contains($propertyName, 'edad')) {
        $this->calcularTotal();
    }
}

    public function render()
    {
        return view('livewire.web.carrito-compra')
            ->layout('layouts.carrito'); 
    }    
}
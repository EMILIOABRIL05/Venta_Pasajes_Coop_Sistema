<?php

namespace App\Livewire\Web;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Viaje;
use App\Models\Pasajero;
use App\Models\Venta;
use App\Models\Boleto;
use App\Models\Asiento;
use App\Support\DescuentoPorEdad; // Clase estática de Manuel
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CarritoCompra extends Component
{

    // Propiedades del Sprint 3
    public $viajeId;
    public $viaje;
    public $asientosSeleccionados = [];
    public $datosPasajeros = []; // Nombre, Cédula, Edad
    public $total = 0;
    public array $seatCategories = [];

    protected $rules = [
        'datosPasajeros.*.nombre' => 'required|string|min:3',
        'datosPasajeros.*.cedula' => 'required|string|digits:10',
        'datosPasajeros.*.edad' => 'required|integer|min:0|max:110',
    ];

    public function mount($viajeId)
    {
        $this->viajeId = $viajeId;
        // Cargamos el viaje con su frecuencia, ruta, bus y boletos vendidos
        $this->viaje = Viaje::with(['frecuencia.ruta', 'bus', 'boletos'])->findOrFail($viajeId);
        $this->seatCategories = $this->viaje->bus
            ? $this->viaje->bus->asientos()->pluck('categoria', 'numero')->mapWithKeys(function ($categoria, $numero) {
                return [(string) $numero => $categoria];
            })->all()
            : [];
    }

public function seleccionarAsiento($numeroAsiento)
{
    if (!$numeroAsiento) return;
    $numeroAsiento = (string) $numeroAsiento;

    if (in_array($numeroAsiento, $this->asientosSeleccionados)) {
        $this->asientosSeleccionados = array_diff($this->asientosSeleccionados, [$numeroAsiento]);
        unset($this->datosPasajeros[$numeroAsiento]);
    } else {
        $this->asientosSeleccionados[] = $numeroAsiento;
        
        // Aseguramos que el precio base exista
            $precioBase = $this->precioBaseAsiento($numeroAsiento);
        
        $this->datosPasajeros[$numeroAsiento] = [
            'nombre' => '',
            'cedula' => '',
            'edad' => '',
            'precio' => $precioBase
        ];
    }
    
    $this->calcularTotal();
}

    public function calcularTotal()
    {
        $this->total = 0;

        foreach ($this->datosPasajeros as $key => $pasajero) {
            $precioBase = $this->precioBaseAsiento($key);

            if ($pasajero['edad'] !== '' && is_numeric($pasajero['edad'])) {
                // Usamos la clase estática de Manuel para el descuento por edad
                $precioFinal = DescuentoPorEdad::precioFinal($precioBase, (int) $pasajero['edad']);
                $this->datosPasajeros[$key]['precio'] = $precioFinal;
                $this->total += $precioFinal;
            } else {
                $this->datosPasajeros[$key]['precio'] = $precioBase;
                $this->total += $precioBase;
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
                'cliente_id' => Auth::id(),
                'total' => $this->total,
                'estado' => 'Pendiente', // Estado inicial de la venta web
                'comprobante' => null,
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
                    'categoria_asiento' => $this->categoriaAsiento($asiento),
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

    private function categoriaAsiento(string $numeroAsiento): string
    {
        $asiento = Asiento::query()
            ->where('bus_id', $this->viaje->bus_id)
            ->where('numero', (int) $numeroAsiento)
            ->first();

        return $asiento?->categoria ?? 'estandar';
    }

    private function precioBaseAsiento(string $numeroAsiento): float
    {
        $precioBase = (float) ($this->viaje->frecuencia->ruta->precio_base ?? 0);

        return $this->categoriaAsiento($numeroAsiento) === 'vip'
            ? round($precioBase * 1.5, 2)
            : round($precioBase, 2);
    }
}
<?php

namespace App\Livewire\Web;

use Livewire\Component;
use App\Models\Viaje;
use App\Models\Pasajero;
use App\Models\Venta;
use App\Models\Boleto;
use App\Support\DescuentoPorEdad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompraWeb extends Component
{
    public $viajeId;
    public $viaje;
    public $asientosSeleccionados = [];
    public $datosPasajeros = []; 
    public $tipoAsiento = 'estandar'; 
    public $recargo = 0.00;
    public $total = 0.00;

    protected $rules = [
        'datosPasajeros.*.nombre' => 'required|string|min:3',
        'datosPasajeros.*.cedula' => 'required|string|digits:10',
        'datosPasajeros.*.edad' => 'required|integer|min:0|max:110',
    ];

    public function mount($viajeId)
    {
        $this->viajeId = $viajeId;
        
        // CÓDIGO LIMPIO: Usamos findOrFail. Si el ID del viaje no existe, Laravel 
        // abortará automáticamente con un error 404, como debe ser en producción.
        $this->viaje = Viaje::with([
            'frecuencia.ruta.origen', 
            'frecuencia.ruta.destino', 
            'bus.categoria', 
            'boletos'
        ])->findOrFail($viajeId);
        
        $this->recargo = 0.00; 
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
            
            // CONEXIÓN REAL: Leemos el precio base dinámicamente
            $precioBase = $this->viaje->frecuencia->ruta->precio_base ?? 0; 
            $precioConRecargo = $precioBase + $this->recargo;

            // LÓGICA UX: Autocompletar solo el primer asiento con los datos del usuario logueado
            $nombreAuto = '';
            $cedulaAuto = '';
            
            if (count($this->asientosSeleccionados) === 1 && Auth::check()) {
                $nombreAuto = Auth::user()->name; 
                // Si en el futuro agregas la cédula a la tabla users, descomenta esta línea:
                // $cedulaAuto = Auth::user()->cedula ?? ''; 
            }

            $this->datosPasajeros[$numeroAsiento] = [
                'nombre' => $nombreAuto,
                'cedula' => $cedulaAuto,
                'edad' => '',
                'precio' => $precioConRecargo
            ];
        }
        
        $this->calcularTotal();
    }

    public function updatedTipoAsiento($value)
    {
        if ($value === 'vip') {
            $this->recargo = 5.00;
        } else {
            $this->recargo = 0.00;
        }

        $this->calcularTotal();
    }

    public function updated($propertyName)
    {
        if (str_contains($propertyName, 'edad') || str_contains($propertyName, 'tipoAsiento')) {
            $this->calcularTotal();
        }
    }

    public function calcularTotal()
    {
        $this->total = 0;
        $precioBase = $this->viaje->frecuencia->ruta->precio_base ?? 0; 
        $precioConRecargo = $precioBase + $this->recargo;

        foreach ($this->datosPasajeros as $key => $pasajero) {
            $edad = $pasajero['edad'];
            if ($edad !== '' && is_numeric($edad)) {
                $precioFinal = DescuentoPorEdad::precioFinal($precioConRecargo, (int) $edad);
                $this->datosPasajeros[$key]['precio'] = $precioFinal;
                $this->total += $precioFinal;
            } else {
                $this->datosPasajeros[$key]['precio'] = $precioConRecargo;
                $this->total += $precioConRecargo;
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

       // 🛡️ EL ESCUDO ANTI-DUPLICADOS CORREGIDO
        // Verificamos si los asientos ya están en la base de datos
        $asientosYaVendidos = Boleto::where('frecuencia_id', $this->viaje->frecuencia_id)
            ->whereIn('numero_asiento', $this->asientosSeleccionados)
            ->pluck('numero_asiento')
            ->unique() // <-- ¡Cura definitiva! Elimina el "3, 3" y lo deja en un solo "3"
            ->toArray();

        if (!empty($asientosYaVendidos)) {
            // Mensaje corto y claro como sugeriste
            $textoAsientos = count($asientosYaVendidos) === 1 
                ? 'El asiento ' . $asientosYaVendidos[0] 
                : 'Los asientos ' . implode(', ', $asientosYaVendidos);

            session()->flash('error', $textoAsientos . ' ya está ocupado. Por favor, selecciona otro.');
            
            // Recargamos el mapa y limpiamos la selección inválida
            $this->viaje->load('boletos');
            $this->asientosSeleccionados = array_diff($this->asientosSeleccionados, $asientosYaVendidos);
            foreach ($asientosYaVendidos as $ocupado) {
                unset($this->datosPasajeros[$ocupado]);
            }
            
            $this->calcularTotal();
            return; 
        }

        try {
            DB::beginTransaction();

            $venta = Venta::create([
                'user_id' => Auth::id(),
                'cliente_id' => Auth::id(),
                'total' => $this->total,
                'estado' => 'Pendiente',
                'comprobante' => null,
            ]);

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
                    'frecuencia_id' => $this->viaje->frecuencia_id,
                    'numero_asiento' => $asiento,
                    'precio_final' => $datos['precio'],
                ]);
            }

            DB::commit();

            $this->asientosSeleccionados = [];
            $this->datosPasajeros = [];
            $this->total = 0;

            session()->flash('success', '¡Compra web realizada con éxito!');
            
            return redirect()->route('pago', ['ventaId' => $venta->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar la compra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('components.compra-web')
            ->layout('layouts.carrito');
    }
}
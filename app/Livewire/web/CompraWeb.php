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
        $this->recargo = 0.00;
    }

    private function getViajeData()
    {
        return Viaje::with([
            'frecuencia.ruta.origen',
            'frecuencia.ruta.destino',
            'bus.categoria',
            'boletos',
        ])->findOrFail($this->viajeId);
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

            $viaje = $this->getViajeData();
            $precioBase = $viaje->frecuencia->ruta->precio_base ?? 0;
            $precioConRecargo = $precioBase + $this->recargo;

            $nombreAuto = '';
            $cedulaAuto = '';

            if (count($this->asientosSeleccionados) === 1 && Auth::check()) {
                $nombreAuto = Auth::user()->name;
                $cedulaAuto = Auth::user()->cedula ?? '';
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
        $this->recargo = ($value === 'vip') ? 5.00 : 0.00;
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
        $viaje = $this->getViajeData();
        $precioBase = $viaje->frecuencia->ruta->precio_base ?? 0;
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

        $redirect = null;
        $errorVerificacion = false;
        $viaje = $this->getViajeData();
        try {
            DB::transaction(function () use (&$redirect, &$errorVerificacion, $viaje) {

                $asientosYaVendidos = Boleto::where('viaje_id', $viaje->id)
                    ->whereIn('numero_asiento', $this->asientosSeleccionados)
                    ->lockForUpdate()
                    ->pluck('numero_asiento')
                    ->unique()
                    ->toArray();

                if (!empty($asientosYaVendidos)) {
                    $textoAsientos = count($asientosYaVendidos) === 1
                        ? 'El asiento ' . $asientosYaVendidos[0]
                        : 'Los asientos ' . implode(', ', $asientosYaVendidos);

                    $this->asientosSeleccionados = array_diff($this->asientosSeleccionados, $asientosYaVendidos);
                    foreach ($asientosYaVendidos as $ocupado) {
                        unset($this->datosPasajeros[$ocupado]);
                    }
                    $this->calcularTotal();

                    session()->flash('error', $textoAsientos . ' ya está ocupado. Por favor, selecciona otro.');
                    $errorVerificacion = true;
                    return;
                }

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
                        'venta_id'       => $venta->id,
                        'pasajero_id'    => $pasajero->id,
                        'viaje_id'       => $viaje->id,
                        'frecuencia_id'  => $viaje->frecuencia_id,
                        'numero_asiento' => $asiento,
                        'precio_final'   => $datos['precio'],
                    ]);
                }

                $this->asientosSeleccionados = [];
                $this->datosPasajeros = [];
                $this->total = 0;

                session()->flash('success', '¡Compra web realizada con éxito!');
                $redirect = redirect()->route('pago', ['ventaId' => $venta->id]);
            });

            if ($errorVerificacion) {
                return;
            }

        } catch (\Illuminate\Database\QueryException $e) {
            $code = (string)$e->getCode();
            $msg = strtolower($e->getMessage());
            if ($code === '23505' || $code === '23000' || $code === '19' || str_contains($msg, 'unique') || str_contains($msg, 'unicidad')) {
                $asientoAfectado = $this->asientosSeleccionados[0] ?? '5';
                session()->flash('error', "El asiento {$asientoAfectado} se vendió antes; recarga el mapa.");
                $this->dispatch('refreshMapa');
                return null;
            }
            throw $e;
        } catch (\Exception $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'unique') || str_contains($msg, 'unicidad')) {
                $asientoAfectado = $this->asientosSeleccionados[0] ?? '5';
                session()->flash('error', "El asiento {$asientoAfectado} se vendió antes; recarga el mapa.");
                $this->dispatch('refreshMapa');
                return null;
            }
            session()->flash('error', 'Error al procesar la compra: ' . $e->getMessage());
            return null;
        }

        return $redirect;
    }

    public function render()
    {
        return view('components.compra-web', [
            'viaje' => $this->getViajeData()
        ])->layout('layouts.carrito');
    }
}
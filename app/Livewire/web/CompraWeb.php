<?php

namespace App\Livewire\Web;

use App\Models\Asiento;
use App\Models\Boleto;
use App\Models\Pasajero;
use App\Models\Venta;
use App\Models\Viaje;
use App\Support\DescuentoPorEdad;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CompraWeb extends Component
{
    public $viajeId;

    public $asientosSeleccionados = [];

    public $datosPasajeros = [];

    public $total = 0.00;

    /**
     * Mapa de categorías por número de asiento.
     * Ejemplo: ['1' => 'estandar', '5' => 'vip', ...]
     */
    public array $categoriasAsientos = [];

    /**
     * Precios base por asiento (ya con recargo de categoría aplicado).
     * Ejemplo: ['1' => 10.00, '5' => 15.00, ...]
     */
    public array $preciosPorAsiento = [];

    /**
     * Asientos ocupados (solo números como strings).
     */
    public array $asientosOcupados = [];

    /**
     * Precio base de la ruta (sin recargos).
     */
    public float $precioBase = 0.00;

    protected $rules = [
        'datosPasajeros.*.nombre' => 'required|string|min:3',
        'datosPasajeros.*.cedula' => 'required|string|digits:10',
        'datosPasajeros.*.edad' => 'required|integer|min:0|max:110',
    ];

    public function mount($viajeId): void
    {
        $this->viajeId = $viajeId;
        $this->cargarMapaAsientos();
    }

    /**
     * Carga el mapa de asientos con categorías reales desde la BD.
     */
    protected function cargarMapaAsientos(): void
    {
        $viaje = $this->getViajeData();

        $this->precioBase = (float) ($viaje->frecuencia->ruta->precio_base ?? 0);

        // Asientos ocupados para este viaje
        $this->asientosOcupados = Boleto::where('viaje_id', $viaje->id)
            ->activos()
            ->pluck('numero_asiento')
            ->map(fn ($s) => (string) $s)
            ->toArray();

        // Categorías reales desde la tabla asientos del bus
        $asientosBus = Asiento::where('bus_id', $viaje->bus_id)
            ->get()
            ->keyBy(fn ($a) => (string) $a->numero);

        $capacidad = $viaje->bus->numero_asientos ?? 40;
        $this->categoriasAsientos = [];
        $this->preciosPorAsiento = [];

        foreach (range(1, $capacidad) as $numero) {
            $key = (string) $numero;
            $asiento = $asientosBus->get($key);

            $categoria = $asiento?->categoria ?? 'estandar';
            $this->categoriasAsientos[$key] = $categoria;

            // Precio con recargo: VIP = base * 1.5, Estandar = base
            $this->preciosPorAsiento[$key] = $categoria === 'vip'
                ? round($this->precioBase * 1.5, 2)
                : round($this->precioBase, 2);
        }
    }

    private function getViajeData()
    {
        return Viaje::with([
            'frecuencia.ruta.origen',
            'frecuencia.ruta.destino',
            'bus',
            'boletos',
        ])->findOrFail($this->viajeId);
    }

    public function seleccionarAsiento($numeroAsiento): void
    {
        if (! $numeroAsiento) {
            return;
        }
        $numeroAsiento = (string) $numeroAsiento;

        if (in_array($numeroAsiento, $this->asientosSeleccionados)) {
            $this->asientosSeleccionados = array_diff($this->asientosSeleccionados, [$numeroAsiento]);
            unset($this->datosPasajeros[$numeroAsiento]);
        } else {
            $this->asientosSeleccionados[] = $numeroAsiento;

            $precioBaseAsiento = $this->preciosPorAsiento[$numeroAsiento] ?? $this->precioBase;

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
                'precio' => $precioBaseAsiento,
                'categoria' => $this->categoriasAsientos[$numeroAsiento] ?? 'estandar',
            ];
        }

        $this->calcularTotal();
    }

    public function updated($propertyName): void
    {
        if (str_contains($propertyName, 'edad')) {
            $this->calcularTotal();
        }
    }

    public function calcularTotal(): void
    {
        $this->total = 0;

        foreach ($this->datosPasajeros as $key => $pasajero) {
            $precioBaseAsiento = $this->preciosPorAsiento[$key] ?? $this->precioBase;
            $edad = $pasajero['edad'];

            if ($edad !== '' && is_numeric($edad)) {
                $precioFinal = DescuentoPorEdad::precioFinal($precioBaseAsiento, (int) $edad);
                $this->datosPasajeros[$key]['precio'] = $precioFinal;
                $this->total += $precioFinal;
            } else {
                $this->datosPasajeros[$key]['precio'] = $precioBaseAsiento;
                $this->total += $precioBaseAsiento;
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

                if (! empty($asientosYaVendidos)) {
                    $textoAsientos = count($asientosYaVendidos) === 1
                        ? 'El asiento '.$asientosYaVendidos[0]
                        : 'Los asientos '.implode(', ', $asientosYaVendidos);

                    $this->asientosSeleccionados = array_diff($this->asientosSeleccionados, $asientosYaVendidos);
                    foreach ($asientosYaVendidos as $ocupado) {
                        unset($this->datosPasajeros[$ocupado]);
                    }
                    $this->calcularTotal();

                    session()->flash('error', $textoAsientos.' ya está ocupado. Por favor, selecciona otro.');
                    $errorVerificacion = true;

                    return;
                }

                $venta = Venta::create([
                    'user_id' => null,
                    'cliente_id' => Auth::id(),
                    'total' => $this->total,
                    'estado' => Venta::ESTADO_PENDIENTE,
                    'canal_venta' => Venta::CANAL_WEB,
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
                        'viaje_id' => $viaje->id,
                        'frecuencia_id' => $viaje->frecuencia_id,
                        'numero_asiento' => $asiento,
                        'categoria_asiento' => $this->categoriasAsientos[$asiento] ?? 'estandar',
                        'precio_final' => $datos['precio'],
                        'estado' => Boleto::ESTADO_ACTIVO,
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

        } catch (QueryException $e) {
            $code = (string) $e->getCode();
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
            session()->flash('error', 'Error al procesar la compra: '.$e->getMessage());

            return null;
        }

        return $redirect;
    }

    public function render()
    {
        return view('components.compra-web', [
            'viaje' => $this->getViajeData(),
        ])->layout('layouts.carrito');
    }
}

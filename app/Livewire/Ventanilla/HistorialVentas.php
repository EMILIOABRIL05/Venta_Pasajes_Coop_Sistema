<?php

namespace App\Livewire\Ventanilla;

use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class HistorialVentas extends Component
{
    use WithPagination;

    // Propiedades de estado para los filtros
    public $busqueda = '';
    public $filtroFecha = 'todas'; // 'hoy', 'mes', 'todas'

    /**
     * Resetea la paginación al actualizar la búsqueda.
     */
    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    /**
     * Resetea la paginación al actualizar el filtro de fecha.
     */
    public function updatingFiltroFecha()
    {
        $this->resetPage();
    }

    /**
     * Asigna un filtro rápido de fecha y resetea la paginación.
     *
     * @param string $filtro
     */
    public function setFiltroFecha($filtro)
    {
        $this->filtroFecha = $filtro;
        $this->resetPage();
    }

    /**
     * Renderiza el componente con los datos y estadísticas procesadas.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Construir la consulta base
        $consulta = Venta::with([
            'user', 
            'boletos.pasajero', 
            'boletos.frecuencia.ruta.origen',
            'boletos.frecuencia.ruta.destino',
            'boletos.frecuencia.viajes.bus'
        ])->latest();

        // Aplicar filtro rápido de fechas
        if ($this->filtroFecha === 'hoy') {
            $consulta->whereDate('created_at', Carbon::today());
        } elseif ($this->filtroFecha === 'mes') {
            $consulta->whereMonth('created_at', Carbon::now()->month)
                     ->whereYear('created_at', Carbon::now()->year);
        }

        // Aplicar búsqueda (nombre de pasajero, placa de bus o código de reserva)
        if (!empty($this->busqueda)) {
            $busqueda = '%' . strtolower($this->busqueda) . '%';

            $consulta->where(function (Builder $c) use ($busqueda) {
                // Buscar por usuario cajero
                $c->whereHas('user', function (Builder $cUsuario) use ($busqueda) {
                    $cUsuario->whereRaw('LOWER(name) LIKE ?', [$busqueda]);
                })
                // O buscar por datos del boleto
                ->orWhereHas('boletos', function (Builder $cBoleto) use ($busqueda) {
                    // Por código de reserva
                    $cBoleto->whereRaw('LOWER(codigo_reserva) LIKE ?', [$busqueda])
                        // Por pasajero
                        ->orWhereHas('pasajero', function (Builder $cPasajero) use ($busqueda) {
                            $cPasajero->whereRaw('LOWER(nombre_completo) LIKE ?', [$busqueda])
                                      ->orWhereRaw('LOWER(cedula) LIKE ?', [$busqueda]);
                        })
                        // Por bus a través de viaje y frecuencia
                        ->orWhereHas('frecuencia.viajes.bus', function (Builder $cBus) use ($busqueda) {
                            $cBus->whereRaw('LOWER(placa) LIKE ?', [$busqueda]);
                        });
                });
            });
        }

        $ventas = $consulta->paginate(10);

        // --- Estadísticas del Panel Superior ---
        $idUsuario = auth()->id();

        // 1. Total histórico recaudado
        $totalHistorico = \App\Models\Venta::where('user_id', $idUsuario)->sum('total');

        // 2. Ruta más vendida
        $rutaMasVendidaObj = \Illuminate\Support\Facades\DB::table('boletos')
            ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
            ->join('frecuencias', 'boletos.frecuencia_id', '=', 'frecuencias.id')
            ->join('rutas', 'frecuencias.ruta_id', '=', 'rutas.id')
            ->join('paradas as origen', 'rutas.origen_id', '=', 'origen.id')
            ->join('paradas as destino', 'rutas.destino_id', '=', 'destino.id')
            ->where('ventas.user_id', $idUsuario)
            ->whereNull('boletos.deleted_at')
            ->select('origen.nombre as origen_nombre', 'destino.nombre as destino_nombre', \Illuminate\Support\Facades\DB::raw('count(boletos.id) as total_boletos'))
            ->groupBy('origen.nombre', 'destino.nombre')
            ->orderByDesc('total_boletos')
            ->first();

        $rutaMasVendida = $rutaMasVendidaObj ? "{$rutaMasVendidaObj->origen_nombre} - {$rutaMasVendidaObj->destino_nombre}" : 'Sin ventas aún';

        // 3. Porcentaje de ocupación promedio en sus ventas
        // Calculado como: (Promedio de boletos vendidos por venta / Capacidad estándar del bus 40) * 100
        $totalVentasUsuario = \App\Models\Venta::where('user_id', $idUsuario)->count();
        $totalBoletosUsuario = \App\Models\Boleto::whereHas('venta', fn($c) => $c->where('user_id', $idUsuario))->count();
        
        $promedioBoletosPorVenta = $totalVentasUsuario > 0 ? ($totalBoletosUsuario / $totalVentasUsuario) : 0;
        $porcentajeOcupacion = min(100, ($promedioBoletosPorVenta / 40) * 100);

        return view('livewire.ventanilla.historial-ventas', [
            'ventas' => $ventas,
            'totalHistorico' => $totalHistorico,
            'rutaMasVendida' => $rutaMasVendida,
            'porcentajeOcupacion' => $porcentajeOcupacion
        ])->layout('components.layouts.app', ['title' => 'Historial de Ventas']);
    }
}

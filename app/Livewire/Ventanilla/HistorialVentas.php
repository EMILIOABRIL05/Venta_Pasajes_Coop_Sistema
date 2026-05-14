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
    public $search = '';
    public $filtroFecha = 'todas'; // 'hoy', 'mes', 'todas'

    // Actualiza la paginación cuando se busca algo
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroFecha()
    {
        $this->resetPage();
    }

    // Funciones para filtros rápidos
    public function setFiltroFecha($filtro)
    {
        $this->filtroFecha = $filtro;
        $this->resetPage();
    }

    public function render()
    {
        // Construir la consulta base
        $query = Venta::with([
            'user', 
            'boletos.pasajero', 
            'boletos.frecuencia.ruta.origen',
            'boletos.frecuencia.ruta.destino',
            'boletos.frecuencia.viajes.bus'
        ])->latest();

        // Aplicar filtro rápido de fechas
        if ($this->filtroFecha === 'hoy') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->filtroFecha === 'mes') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        // Aplicar búsqueda (nombre de pasajero, placa de bus o código de reserva)
        if (!empty($this->search)) {
            $search = '%' . strtolower($this->search) . '%';

            $query->where(function (Builder $q) use ($search) {
                // Buscar por usuario cajero
                $q->whereHas('user', function (Builder $qUser) use ($search) {
                    $qUser->whereRaw('LOWER(name) LIKE ?', [$search]);
                })
                // O buscar por datos del boleto
                ->orWhereHas('boletos', function (Builder $qBoleto) use ($search) {
                    // Por código de reserva
                    $qBoleto->whereRaw('LOWER(codigo_reserva) LIKE ?', [$search])
                        // Por pasajero
                        ->orWhereHas('pasajero', function (Builder $qPasajero) use ($search) {
                            $qPasajero->whereRaw('LOWER(nombre_completo) LIKE ?', [$search])
                                      ->orWhereRaw('LOWER(cedula) LIKE ?', [$search]);
                        })
                        // Por bus a través de viaje y frecuencia
                        ->orWhereHas('frecuencia.viajes.bus', function (Builder $qBus) use ($search) {
                            $qBus->whereRaw('LOWER(placa) LIKE ?', [$search]);
                        });
                });
            });
        }

        $ventas = $query->paginate(10);

        // --- Estadísticas del Panel Superior ---
        $userId = auth()->id();

        // 1. Total histórico recaudado
        $totalHistorico = \App\Models\Venta::where('user_id', $userId)->sum('total');

        // 2. Ruta más vendida
        $rutaMasVendidaObj = \Illuminate\Support\Facades\DB::table('boletos')
            ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
            ->join('frecuencias', 'boletos.frecuencia_id', '=', 'frecuencias.id')
            ->join('rutas', 'frecuencias.ruta_id', '=', 'rutas.id')
            ->join('paradas as origen', 'rutas.origen_id', '=', 'origen.id')
            ->join('paradas as destino', 'rutas.destino_id', '=', 'destino.id')
            ->where('ventas.user_id', $userId)
            ->whereNull('boletos.deleted_at')
            ->select('origen.nombre as origen_nombre', 'destino.nombre as destino_nombre', \Illuminate\Support\Facades\DB::raw('count(boletos.id) as total_boletos'))
            ->groupBy('origen.nombre', 'destino.nombre')
            ->orderByDesc('total_boletos')
            ->first();

        $rutaMasVendida = $rutaMasVendidaObj ? "{$rutaMasVendidaObj->origen_nombre} - {$rutaMasVendidaObj->destino_nombre}" : 'Sin ventas aún';

        // 3. Porcentaje de ocupación promedio en sus ventas
        // Calculado como: (Promedio de boletos vendidos por venta / Capacidad estándar del bus 40) * 100
        $totalVentasUser = \App\Models\Venta::where('user_id', $userId)->count();
        $totalBoletosUser = \App\Models\Boleto::whereHas('venta', fn($q) => $q->where('user_id', $userId))->count();
        
        $promedioBoletosPorVenta = $totalVentasUser > 0 ? ($totalBoletosUser / $totalVentasUser) : 0;
        $porcentajeOcupacion = min(100, ($promedioBoletosPorVenta / 40) * 100);

        return view('livewire.ventanilla.historial-ventas', [
            'ventas' => $ventas,
            'totalHistorico' => $totalHistorico,
            'rutaMasVendida' => $rutaMasVendida,
            'porcentajeOcupacion' => $porcentajeOcupacion
        ])->layout('components.layouts.app', ['title' => 'Historial de Ventas']);
    }
}

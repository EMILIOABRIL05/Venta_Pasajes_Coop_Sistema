<?php

namespace App\Livewire;

use Livewire\Component;
use App\Livewire\Traits\RequiresRole;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class AdminPanel extends Component
{
    use RequiresRole;

    public function mount()
    {
        // Requiere rol 'admin' para acceder a este componente
        $this->requireRole('admin');
    }

    public function render()
    {
        $ventasData = Venta::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => $item->date,
                    'total' => (float) $item->total_sales
                ];
            });

        $rutasData = DB::table('boletos')
            ->join('frecuencias', 'boletos.frecuencia_id', '=', 'frecuencias.id')
            ->join('rutas', 'frecuencias.ruta_id', '=', 'rutas.id')
            ->join('paradas as origen', 'rutas.origen_id', '=', 'origen.id')
            ->join('paradas as destino', 'rutas.destino_id', '=', 'destino.id')
            ->select(DB::raw("CONCAT(origen.nombre, ' - ', destino.nombre) as ruta"), DB::raw('COUNT(boletos.id) as cantidad'))
            ->groupBy('ruta')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get();

        return view('livewire.admin-panel', [
            'ventasPorDia' => json_encode($ventasData),
            'rutasPopulares' => json_encode($rutasData)
        ]);
    }
}


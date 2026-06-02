<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\RequiresRole;
use App\Models\Bus;
use App\Models\CategoriaAsiento;
use App\Models\Frecuencia;
use App\Models\Parada;
use App\Models\Ruta;
use Livewire\Component;

class DatosEntregaPanel extends Component
{
    use RequiresRole;

    public function mount(): void
    {
        $this->requireRole('admin');
    }

    public function render()
    {
        $rutas = Ruta::query()
            ->with(['origen', 'destino'])
            ->withCount('frecuencias')
            ->orderBy('id')
            ->get();

        return view('livewire.admin.datos-entrega-panel', [
            'totales' => [
                'paradas' => Parada::query()->count(),
                'rutas' => Ruta::query()->count(),
                'frecuencias' => Frecuencia::query()->count(),
                'buses' => Bus::query()->count(),
                'categorias' => CategoriaAsiento::query()->count(),
            ],
            'rutas' => $rutas,
            'categorias' => CategoriaAsiento::query()
                ->orderBy('orden')
                ->get(),
            'buses' => Bus::query()
                ->orderBy('placa')
                ->get(),
        ]);
    }
}

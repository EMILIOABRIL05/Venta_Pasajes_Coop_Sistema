<?php

namespace App\Livewire\Operativa;

use App\Models\Frecuencia;
use App\Models\Ruta;
use Livewire\Component;
use Livewire\WithPagination;

class FrecuenciasCrud extends Component
{
    use WithPagination;

    public $ruta_id, $hora_salida, $frecuencia_id;
    public $isOpen = false;
    public $search = '';

    protected $rules = [
        'ruta_id' => 'required|exists:rutas,id',
        'hora_salida' => 'required',
    ];

    public function mount()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasPermissionTo('manage_frecuencias')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }

    public function render()
    {
        $frecuencias = Frecuencia::with('ruta')
            ->whereHas('ruta', function($query) {
                $query->whereHas('origen', function($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%');
                })->orWhereHas('destino', function($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $rutas = Ruta::with(['origen', 'destino'])->get();

        return view('livewire.operativa.frecuencias-crud', [
            'frecuencias' => $frecuencias,
            'rutas' => $rutas,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->ruta_id = '';
        $this->hora_salida = '';
        $this->frecuencia_id = '';
    }

    public function store()
    {
        $this->validate();

        Frecuencia::updateOrCreate(['id' => $this->frecuencia_id], [
            'ruta_id' => $this->ruta_id,
            'hora_salida' => $this->hora_salida,
        ]);

        session()->flash('message', $this->frecuencia_id ? 'Frecuencia actualizada correctamente.' : 'Frecuencia creada correctamente.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $frecuencia = Frecuencia::findOrFail($id);
        $this->frecuencia_id = $id;
        $this->ruta_id = $frecuencia->ruta_id;
        $this->hora_salida = $frecuencia->hora_salida;

        $this->openModal();
    }

    public function delete($id)
    {
        Frecuencia::find($id)->delete();
        session()->flash('message', 'Frecuencia eliminada correctamente.');
    }
}

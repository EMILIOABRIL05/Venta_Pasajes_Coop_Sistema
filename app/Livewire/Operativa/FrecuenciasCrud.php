<?php

namespace App\Livewire\Operativa;

use Livewire\Component;
use App\Models\Frecuencia;
use App\Models\Ruta;

class FrecuenciasCrud extends Component
{
    public $ruta_id;
    public $hora_salida;
    public $frecuencia_id;

    protected $rules = [
        'ruta_id' => 'required|exists:rutas,id',
        'hora_salida' => 'required',
    ];

    public function render()
    {
        $rutas = Ruta::all();
        $frecuencias = Frecuencia::with('ruta')->get();
        return view('livewire.operativa.frecuencias-crud', compact('rutas', 'frecuencias'));
    }

    public function guardar()
    {
        $this->validate();

        if ($this->frecuencia_id) {
            $frecuencia = Frecuencia::find($this->frecuencia_id);
            $frecuencia->update([
                'ruta_id' => $this->ruta_id,
                'hora_salida' => $this->hora_salida,
            ]);
            session()->flash('message', 'Frecuencia actualizada exitosamente.');
        } else {
            Frecuencia::create([
                'ruta_id' => $this->ruta_id,
                'hora_salida' => $this->hora_salida,
            ]);
            session()->flash('message', 'Frecuencia creada exitosamente.');
        }

        $this->limpiarCampos();
    }

    public function editar($id)
    {
        $frecuencia = Frecuencia::find($id);
        $this->frecuencia_id = $frecuencia->id;
        $this->ruta_id = $frecuencia->ruta_id;
        $this->hora_salida = $frecuencia->hora_salida;
    }

    public function eliminar($id)
    {
        Frecuencia::find($id)->delete();
        session()->flash('message', 'Frecuencia eliminada exitosamente.');
        $this->limpiarCampos();
    }

    public function limpiarCampos()
    {
        $this->ruta_id = null;
        $this->hora_salida = null;
        $this->frecuencia_id = null;
    }
}

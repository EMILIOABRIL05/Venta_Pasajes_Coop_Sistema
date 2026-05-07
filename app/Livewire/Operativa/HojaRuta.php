<?php

namespace App\Livewire\Operativa;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Frecuencia;
use App\Models\Bus;
use App\Models\Viaje;

class HojaRuta extends Component
{
    public $fecha;
    public $frecuencia_id;
    public $bus_id;
    public $viajes;

    public $buses = [];
    public $frecuencias = [];

    protected $rules = [
        'fecha' => 'required|date',
        'frecuencia_id' => 'required|exists:frecuencias,id',
        'bus_id' => 'required|exists:buses,id',
    ];

    public function mount()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'oficinista'])) {
            abort(403, 'Unauthorized action.');
        }
        $this->loadBuses();
        $this->loadFrecuencias();
        $this->loadViajes();
    }

    public function loadBuses()
    {
        $this->buses = Bus::disponible()->get();
    }

    public function loadFrecuencias()
    {
        $this->frecuencias = Frecuencia::with('ruta.origen', 'ruta.destino')->get();
    }

    public function saveViaje()
    {
        $this->validate();

        // Bloqueo de Estado
        $bus = Bus::find($this->bus_id);
        if ($bus->estado !== 'disponible') {
            session()->flash('error', 'El bus seleccionado no está disponible.');
            return;
        }

        // 1. Obtener la frecuencia que se intenta programar para conocer su hora de salida
        $frecuenciaNueva = Frecuencia::findOrFail($this->frecuencia_id);
        $horaNueva = $frecuenciaNueva->hora_salida;

        // 2. Bloqueo Anti-Clonación Real:
        // Verificar si existe algún viaje del mismo bus en la misma fecha, 
        // cuya frecuencia asociada tenga la misma hora de salida.
        $existingViaje = Viaje::where('fecha', $this->fecha)
            ->where('bus_id', $this->bus_id)
            ->whereHas('frecuencia', function ($q) use ($horaNueva) {
                $q->where('hora_salida', $horaNueva);
            })
            ->exists();

        if ($existingViaje) {
            session()->flash('error', 'El bus ya está ocupado en otro viaje programado a la misma hora.');
            return;
        }

        Viaje::create([
            'fecha' => $this->fecha,
            'frecuencia_id' => $this->frecuencia_id,
            'bus_id' => $this->bus_id,
            'estado' => 'programado',
        ]);

        $this->reset(['fecha', 'frecuencia_id', 'bus_id']);
        session()->flash('message', 'Viaje generado exitosamente.');
        $this->loadViajes();
    }

    public function loadViajes()
    {
        $this->viajes = Viaje::with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus'])->latest()->get();
    }

    public function render()
    {
        return view('livewire.operativa.hoja-ruta')->layout('layouts.app');
    }
}

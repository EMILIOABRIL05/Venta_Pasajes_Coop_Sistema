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
        $this->frecuencias = Frecuencia::with('ruta')->get();
    }

    public function saveViaje()
    {
        $this->validate();

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
        $this->viajes = Viaje::with(['frecuencia.ruta', 'bus'])->latest()->get();
    }

    public function render()
    {
        return view('livewire.operativa.hoja-ruta')->layout('layouts.app');
    }
}

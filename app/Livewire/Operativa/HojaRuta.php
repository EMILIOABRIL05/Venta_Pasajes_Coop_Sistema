<?php

namespace App\Livewire\Operativa;

use App\Models\Bus;
use App\Models\Frecuencia;
use App\Models\User;
use App\Models\Viaje;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HojaRuta extends Component
{
    public $fecha;

    public $frecuencia_id;

    public $bus_id;

    public $chofer_user_id = null;

    public $viajes;

    public $buses = [];

    public $frecuencias = [];

    public $choferes = [];

    protected $rules = [
        'fecha' => 'required|date',
        'frecuencia_id' => 'required|exists:frecuencias,id',
        'bus_id' => 'required|exists:buses,id',
        'chofer_user_id' => 'nullable|exists:users,id',
    ];

    public function mount()
    {
        if (! Auth::user()->hasAnyRole(['admin', 'oficinista'])) {
            abort(403, 'Unauthorized action.');
        }
        $this->loadBuses();
        $this->loadFrecuencias();
        $this->loadChoferes();
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

    public function loadChoferes(): void
    {
        $this->choferes = User::query()
            ->role('chofer')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function saveViaje()
    {
        if ($this->chofer_user_id === '' || $this->chofer_user_id === '0') {
            $this->chofer_user_id = null;
        }

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
            $this->dispatch('flash-message', message: 'El bus ya está ocupado en otro viaje programado a la misma hora.', type: 'error');

            return;
        }

        // Bloqueo de Solapamiento de Horarios
        $busOcupado = Viaje::join('frecuencias', 'viajes.frecuencia_id', '=', 'frecuencias.id')
            ->where('viajes.bus_id', $this->bus_id)
            ->where('viajes.fecha', $this->fecha)
            ->where('frecuencias.hora_salida', Frecuencia::find($this->frecuencia_id)->hora_salida)
            ->where('viajes.estado', '!=', 'cancelado')
            ->exists();

        if ($busOcupado) {
            $this->dispatch('flash-message', message: 'Este bus ya tiene un viaje asignado para esta hora', type: 'error');

            return;
        }

        // Bloqueo de Solapamiento de Chofer (mismo patrón que el bus)
        if ($this->chofer_user_id) {
            $choferOcupado = Viaje::join('frecuencias', 'viajes.frecuencia_id', '=', 'frecuencias.id')
                ->where('viajes.chofer_user_id', $this->chofer_user_id)
                ->where('viajes.fecha', $this->fecha)
                ->where('frecuencias.hora_salida', Frecuencia::find($this->frecuencia_id)->hora_salida)
                ->whereNotIn('viajes.estado', ['Finalizada', 'cancelado'])
                ->exists();

            if ($choferOcupado) {
                $this->dispatch('flash-message', message: 'Este chofer ya tiene un viaje asignado para esta fecha y hora.', type: 'error');

                return;
            }
        }

        // Bloqueo de Frecuencia Duplicada (exclusividad de horario por día)
        $frecuenciaDuplicada = Viaje::where('frecuencia_id', $this->frecuencia_id)
            ->where('fecha', $this->fecha)
            ->whereNotIn('estado', ['Finalizada', 'cancelado'])
            ->exists();

        if ($frecuenciaDuplicada) {
            $this->dispatch('flash-message', message: 'Ya existe un viaje programado para esta ruta y horario en la fecha seleccionada.', type: 'error');

            return;
        }

        Viaje::create([
            'fecha' => $this->fecha,
            'frecuencia_id' => $this->frecuencia_id,
            'bus_id' => $this->bus_id,
            'chofer_user_id' => $this->chofer_user_id ?: null,
            'estado' => 'En Terminal',
        ]);

        $this->reset(['fecha', 'frecuencia_id', 'bus_id', 'chofer_user_id']);
        $this->dispatch('flash-message', message: 'Viaje generado exitosamente.', type: 'success');
        $this->loadViajes();
    }

    public function loadViajes()
    {
        $this->viajes = Viaje::with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus', 'chofer'])->latest()->get();
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $estadosPermitidos = ['En Terminal', 'En Curso', 'Finalizada', 'programado', 'cancelado'];

        $viaje = Viaje::find($id);

        if (! $viaje) {
            $this->dispatch('flash-message', message: 'Viaje no encontrado.', type: 'error');

            return;
        }

        if (! in_array($nuevoEstado, $estadosPermitidos)) {
            $this->dispatch('flash-message', message: 'Estado no válido.', type: 'error');

            return;
        }

        if (in_array($viaje->estado, ['Finalizada', 'cancelado'])) {
            $this->dispatch('flash-message', message: 'Este viaje ya ha concluido y no puede ser modificado.', type: 'error');

            return;
        }

        if ($viaje->estado === 'En Curso' && $nuevoEstado !== 'Finalizada') {
            $this->dispatch('flash-message', message: 'Un viaje en curso solo puede ser finalizado.', type: 'error');

            return;
        }

        if ($viaje->estado === 'En Terminal' && $nuevoEstado === 'Finalizada') {
            $this->dispatch('flash-message', message: 'El viaje debe pasar por "En Curso" antes de ser finalizado.', type: 'error');

            return;
        }

        DB::transaction(function () use ($viaje, $nuevoEstado) {
            $viaje->update(['estado' => $nuevoEstado]);

            if ($nuevoEstado === 'En Curso') {
                $viaje->bus()->update(['estado' => 'en_ruta']);
            } elseif (in_array($nuevoEstado, ['Finalizada', 'cancelado'])) {
                $viaje->bus()->update(['estado' => 'disponible']);
            }
        });

        $this->dispatch('flash-message', message: 'Estado actualizado correctamente.', type: 'success');
        $this->loadViajes();
    }

    public function render()
    {
        return view('livewire.operativa.hoja-ruta')->layout('layouts.app');
    }
}

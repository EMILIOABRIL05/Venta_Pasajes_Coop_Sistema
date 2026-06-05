<?php

namespace App\Livewire;

use App\Models\Parada;
use App\Models\Viaje;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Componente Livewire: BuscadorPasajes
 *
 * Sprint 2 — Tarea: Conectar buscador público a BD
 * Responsable: Web / Estudiante 5
 */
class BuscadorPasajes extends Component
{
    // ── Campos del formulario ──────────────────────────────────────────────

    /** Ciudad / terminal de origen */
    public string $origen = '';

    /** Ciudad / terminal de destino */
    public string $destino = '';

    /** Fecha deseada de viaje (formato Y-m-d) */
    public string $fecha = '';

    // ── Resultados de la búsqueda ──────────────────────────────────────────

    public $viajesEncontrados = [];

    public bool $busquedaRealizada = false;

    // ── Próximos viajes (sección pre-búsqueda) ─────────────────────────────

    public Collection $proximosViajes;

    // ── Ciclo de vida y Métodos ────────────────────────────────────────────

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->proximosViajes = $this->cargarProximosViajes();
    }

    public function buscar(): void
    {
        $this->validate([
            'origen' => 'required|string',
            'destino' => 'required|string',
            'fecha' => 'required|date',
        ]);

        $this->viajesEncontrados = Viaje::with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus'])
            ->whereDate('fecha', $this->fecha)
            ->whereIn('estado', ['programado', 'En Terminal'])
            ->whereHas('frecuencia.ruta.origen', function ($q) {
                $q->where('id', $this->origen);
            })
            ->whereHas('frecuencia.ruta.destino', function ($q) {
                $q->where('id', $this->destino);
            })
            ->get()
            ->map(function ($viaje) {
                $viaje->asientos_disponibles = $this->calcularAsientosDisponibles($viaje);

                return $viaje;
            });

        $this->busquedaRealizada = true;
    }

    /**
     * Carga los viajes programados para las próximas 48 horas.
     */
    protected function cargarProximosViajes(): Collection
    {
        return Viaje::with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus'])
            ->whereBetween('fecha', [now(), now()->addHours(48)])
            ->whereIn('estado', ['programado', 'En Terminal'])
            ->orderBy('fecha', 'asc')
            ->get()
            ->sortBy(fn ($v) => $v->frecuencia?->hora_salida ?? '23:59:59')
            ->map(function ($viaje) {
                $viaje->asientos_disponibles = $this->calcularAsientosDisponibles($viaje);

                return $viaje;
            })
            ->values();
    }

    /**
     * Calcula los asientos disponibles para un viaje.
     */
    protected function calcularAsientosDisponibles($viaje): int
    {
        $capacidad = $viaje->bus?->numero_asientos ?? 40;
        $ocupados = $viaje->boletos()->activos()->count();

        return max(0, $capacidad - $ocupados);
    }

    public function render()
    {
        return view('livewire.buscador-pasajes', [
            'paradas' => Parada::all(),
        ]);
    }
}

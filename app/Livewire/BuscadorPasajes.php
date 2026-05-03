<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Viaje;
use App\Models\Parada;

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

    /** Número de pasajeros */
    public int $pasajeros = 1;

    // ── Resultados de la búsqueda ──────────────────────────────────────────

    public $viajesEncontrados = [];
    public bool $busquedaRealizada = false;

    // ── Ciclo de vida y Métodos ────────────────────────────────────────────

    public function mount()
    {
        // Inicia con la fecha de hoy
        $this->fecha = now()->toDateString();
    }

   public function buscar()
{
    $this->validate([
        'origen' => 'required|string',
        'destino' => 'required|string',
        'fecha' => 'required|date',
    ]);

    // AQUÍ ES EL CAMBIO:
    $this->viajesEncontrados = Viaje::with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus'])
        ->whereDate('fecha', $this->fecha)
        ->whereHas('frecuencia.ruta.origen', function ($q) {
            // Cambiamos 'ciudad' por 'id' porque $this->origen ahora tiene el UUID del select
            $q->where('id', $this->origen); 
        })
        ->whereHas('frecuencia.ruta.destino', function ($q) {
            // Lo mismo para el destino
            $q->where('id', $this->destino); 
        })
        ->get();

    $this->busquedaRealizada = true;
}

    public function render()
{
    return view('livewire.buscador-pasajes', [
        // Esto envía las ciudades de la BD a tu formulario
        'paradas' => Parada::all() 
    ]);
}
}
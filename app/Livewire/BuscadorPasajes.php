<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Componente Livewire: BuscadorPasajes
 *
 * Sprint 1 — Tarea: Maqueta de la interfaz pública del buscador de pasajes.
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

    // ── Ciclo de vida y Métodos ────────────────────────────────────────────

    public function mount()
    {
        // Inicia con la fecha de hoy
        $this->fecha = now()->toDateString();
    }

    public function buscar()
    {
        
    }

    public function render()
    {
        return view('livewire.buscador-pasajes');
    }
}
<?php

namespace App\Livewire\Chofer;

use App\Livewire\Traits\RequiresRole;
use Livewire\Component;

class PanelPrincipal extends Component
{
    use RequiresRole;

    public string $origen = 'Ambato';
    public string $destino = 'Quito';
    public string $horaSalida = '08:30';
    public string $numeroBus = 'Unidad 12';
    public string $placaBus = 'ABC-1234';
    public int $pasajerosAbordo = 21;
    public int $capacidadTotal = 40;
    public string $estadoActual = 'en_terminal';

    public function mount(): void
    {
        $this->requireRole(['chofer', 'admin']);
    }

    public function cambiarEstado(string $estado): void
    {
        // Placeholder de Sprint 4: la lógica real la integra Operativa (Estudiante 3).
        $this->estadoActual = $estado;
    }

    public function render()
    {
        return view('livewire.chofer.panel-principal')->layout('layouts.app');
    }
}

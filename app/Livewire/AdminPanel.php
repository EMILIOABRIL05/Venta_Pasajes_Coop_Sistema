<?php

namespace App\Livewire;

use Livewire\Component;
use App\Livewire\Traits\RequiresRole;

class AdminPanel extends Component
{
    use RequiresRole;

    public function mount()
    {
        // Requiere rol 'admin' para acceder a este componente
        $this->requireRole('admin');
    }

    public function render()
    {
        return view('livewire.admin-panel');
    }
}

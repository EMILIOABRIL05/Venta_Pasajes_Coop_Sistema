<?php

namespace App\Livewire\Traits;

trait RequiresRole
{
    /**
     * Lanza 403 si el usuario no tiene alguno de los roles indicados.
     * Usar desde `mount()` en componentes Livewire: `$this->requireRole('admin');`
     */
    protected function requireRole(string|array $roles): void
    {
        $rolesArr = is_array($roles) ? $roles : explode('|', $roles);

        if (! auth()->check() || ! auth()->user()->hasAnyRole($rolesArr)) {
            abort(403);
        }
    }
}

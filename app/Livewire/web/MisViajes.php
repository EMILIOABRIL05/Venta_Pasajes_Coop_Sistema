<?php

namespace App\Livewire\Web;

use Livewire\Component;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;

class MisViajes extends Component
{
    public function render()
    {
        $compras = Venta::where('cliente_id', Auth::id())
            ->with([
                'boletos.pasajero', 
                'boletos.frecuencia.ruta'
            ])
            ->latest()
            ->paginate(10);

        // Usamos tu layout público del carrito
        return view('livewire.web.mis-viajes', [
            'compras' => $compras
        ])->layout('layouts.carrito'); 
    }
}
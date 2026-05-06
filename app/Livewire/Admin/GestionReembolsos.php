<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Reembolso;
use Illuminate\Support\Facades\DB;

class GestionReembolsos extends Component
{
    public function aprobar($reembolsoId)
    {
        $reembolso = Reembolso::find($reembolsoId);
        if (!$reembolso || $reembolso->estado !== 'pendiente') {
            session()->flash('error', 'Solicitud no encontrada o no pendiente.');
            return;
        }

        DB::transaction(function () use ($reembolso) {
            // Cambiar estado del reembolso
            $reembolso->update([
                'estado' => 'aprobado',
                'fecha_resolucion' => now(),
            ]);

            // Anular los boletos de la venta (soft delete)
            $reembolso->venta->boletos->each(function ($boleto) {
                $boleto->delete();
            });
        });

        session()->flash('message', 'Reembolso aprobado y boletos anulados.');
        $this->emit('refreshTable');
    }

    public function rechazar($reembolsoId)
    {
        $reembolso = Reembolso::find($reembolsoId);
        if (!$reembolso || $reembolso->estado !== 'pendiente') {
            session()->flash('error', 'Solicitud no encontrada o no pendiente.');
            return;
        }

        $reembolso->update([
            'estado' => 'rechazado',
            'fecha_resolucion' => now(),
        ]);

        session()->flash('message', 'Reembolso rechazado.');
        $this->emit('refreshTable');
    }

    public function render()
    {
        $reembolsos = Reembolso::with('venta')->where('estado', 'pendiente')->get();

        return view('livewire.admin.gestion-reembolsos', compact('reembolsos'));
    }
}
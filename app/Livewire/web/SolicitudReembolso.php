<?php

namespace App\Livewire\Web;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Boleto;
use App\Models\Reembolso;
use App\Models\BoletoValidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SolicitudReembolso extends Component
{
    use WithFileUploads;

    public $boleto_uuid;
    public $motivo;
    public $evidencia;

    protected $rules = [
        'boleto_uuid' => 'required|string|exists:boletos,id',
        'motivo' => 'required|string|max:255',
        'evidencia' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
    ];

    protected $messages = [
        'boleto_uuid.exists' => 'El boleto no existe.',
        'evidencia.mimes' => 'La evidencia debe ser un archivo PDF, JPG, JPEG o PNG.',
        'evidencia.max' => 'La evidencia no debe superar los 10MB.',
    ];

    public function updatedBoletoUuid()
    {
        $this->validateOnly('boleto_uuid');
    }

    public function submit()
    {
        $this->validate();

        // Verificar que el boleto existe y no ha sido validado
        $boleto = Boleto::find($this->boleto_uuid);
        if (!$boleto) {
            $this->addError('boleto_uuid', 'El boleto no existe.');
            return;
        }

        // Verificar si ya ha sido validado (tiene BoletoValidacion con estado 'validado')
        $validacion = BoletoValidacion::where('boleto_id', $boleto->id)->where('estado', 'validado')->first();
        if ($validacion) {
            $this->addError('boleto_uuid', 'El boleto ya ha sido validado y no puede ser reembolsado.');
            return;
        }

        // Verificar si ya existe una solicitud de reembolso pendiente o aprobada
        $reembolsoExistente = Reembolso::where('venta_id', $boleto->venta_id)->whereIn('estado', ['pendiente', 'aprobado'])->first();
        if ($reembolsoExistente) {
            $this->addError('boleto_uuid', 'Ya existe una solicitud de reembolso para este boleto.');
            return;
        }

        DB::transaction(function () use ($boleto) {
            $evidenciaPath = null;
            if ($this->evidencia) {
                $evidenciaPath = $this->evidencia->store('reembolsos', 'public');
            }

            Reembolso::create([
                'venta_id' => $boleto->venta_id,
                'monto' => $boleto->venta->total,
                'motivo' => $this->motivo,
                'estado' => 'pendiente',
                'comentarios' => $evidenciaPath ? 'Evidencia: ' . $evidenciaPath : null,
                'fecha_solicitud' => now(),
            ]);
        });

        session()->flash('message', 'Solicitud de reembolso enviada exitosamente.');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.web.solicitud-reembolso');
    }
}
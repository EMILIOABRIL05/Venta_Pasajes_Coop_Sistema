<?php

namespace App\Livewire\Web;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PagoWeb extends Component
{
    use WithFileUploads;

    public $venta;
    public $comprobante;
    public $banco_destino = "Banco del Austro - Cuenta Corriente: 123456789";
    public $mensaje_exito = "";

    /**
     * Inicializa el componente con la venta creada.
     */
    public function mount($ventaId)
    {
        $this->venta = Venta::where('cliente_id', Auth::id())
                            ->findOrFail($ventaId);

        // Si ya tiene comprobante o está validada, redirigir o mostrar mensaje
        if ($this->venta->estado !== 'Pendiente') {
            session()->flash('info', 'Esta venta ya ha sido procesada o está en validación.');
        }
    }

    /**
     * Reglas de validación para el archivo.
     */
    protected function rules()
    {
        return [
            'comprobante' => 'required|image|max:10240', // Máximo 10MB
        ];
    }

    /**
     * Guarda el comprobante y actualiza el estado de la venta.
     */
    public function guardarPago()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // Almacenar el archivo
                $nombreArchivo = 'comprobante_' . $this->venta->id . '_' . time() . '.' . $this->comprobante->extension();
                $ruta = $this->comprobante->storeAs('comprobantes', $nombreArchivo, 'public');

                // Actualizar la venta
                $this->venta->update([
                    'comprobante' => $ruta,
                    'estado' => 'Pendiente de Validación',
                ]);
            });

            $this->mensaje_exito = "¡Comprobante subido con éxito! Su pago está siendo validado por nuestro equipo.";
            
            // Opcional: Notificar al usuario o redirigir
            session()->flash('success', $this->mensaje_exito);
            
            return redirect()->route('mis-viajes');

        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage());
        }
    }

    public function render()
{
    $metodosPago = [
        'transferencia' => 'Transferencia Bancaria',
        'efectivo' => 'Pago en Ventanilla'
    ];

    return view('livewire.web.pago-web', [
        'metodosPago' => $metodosPago
    ]);
}
}

<?php

namespace App\Livewire\Web;

use Livewire\Component;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use App\Models\Boleto;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    /**
     * Descarga el boleto en formato PDF.
     * Solo si el boleto pertenece al usuario autenticado.
     */
    public function descargarBoleto($uuid)
    {
        // Validar que el boleto pertenece al usuario autenticado
        $boleto = Boleto::with(['pasajero', 'frecuencia.ruta.origen', 'frecuencia.ruta.destino'])
            ->where('id', $uuid)
            ->whereHas('venta', function ($query) {
                $query->where('cliente_id', Auth::id());
            })
            ->first();

        if (!$boleto) {
            session()->flash('error', 'No tienes permiso para descargar este boleto.');
            return;
        }

        // Generar QR en formato SVG base64; el lector envia este UUID al endpoint POST.
        $qrCode = base64_encode(QrCode::format('svg')->size(150)->generate($boleto->id));

        $data = [
            'boleto' => $boleto,
            'qrCode' => $qrCode,
        ];

        $pdf = Pdf::loadView('ventas.boleto_pdf', $data);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "Boleto-Ambato-{$boleto->id}.pdf");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VentaController extends Controller
{
    /**
     * Descargar el boleto en PDF con código QR.
     */
    public function descargarBoleto($id)
    {
        // Buscar el boleto por UUID
        $boleto = Boleto::with(['venta', 'pasajero'])->findOrFail($id);

        // Generar el código QR con el UUID del boleto
        $qrCode = QrCode::size(200)->generate($boleto->id);

        // Preparar datos para la vista
        $data = [
            'boleto' => $boleto,
            'qrCode' => $qrCode,
        ];

        // Generar el PDF usando la vista
        $pdf = Pdf::loadView('ventas.boleto_pdf', $data);

        // Descargar el PDF
        return $pdf->download('boleto_' . $boleto->id . '.pdf');
    }
}
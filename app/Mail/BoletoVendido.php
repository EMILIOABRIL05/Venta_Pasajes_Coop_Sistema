<?php

namespace App\Mail;

use App\Models\Boleto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BoletoVendido extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $boleto;

    /**
     * Create a new message instance.
     */
    public function __construct(Boleto $boleto)
    {
        $this->boleto = $boleto;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Boleto de Viaje - Cooperativa Ambato',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.boleto_vendido',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Cargar las relaciones necesarias para el PDF
        $this->boleto->loadMissing(['venta', 'pasajero', 'frecuencia.ruta.origen', 'frecuencia.ruta.destino']);

        // Generar el código QR
        $qrCode = QrCode::size(200)->generate($this->boleto->id);

        // Generar el PDF del boleto reutilizando la vista del Sprint anterior
        $pdf = Pdf::loadView('ventas.boleto_pdf', [
            'boleto' => $this->boleto,
            'qrCode' => $qrCode,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'boleto_' . ($this->boleto->pasajero->cedula ?? $this->boleto->id) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

<?php

namespace App\Listeners;

use App\Events\TicketPurchased;
use App\Mail\TicketPurchasedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendTicketPurchasedEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TicketPurchased $event): void
    {
        // Se asume que el pasajero tiene un correo registrado
        $correo = $event->boleto->pasajero->correo ?? 'pasajero@ejemplo.com';
        Mail::to($correo)->send(new TicketPurchasedMail($event->boleto));
    }
}

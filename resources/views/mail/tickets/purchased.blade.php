<x-mail::message>
# Tu Boleto de Viaje Confirmado

Hola **{{ $boleto->pasajero->nombre_completo ?? 'Pasajero' }}**,

¡Gracias por viajar con nosotros! Tu compra ha sido procesada con éxito. Aquí tienes los detalles de tu viaje:

<x-mail::panel>
**Detalles del Boleto:**
- **Ruta:** {{ $boleto->frecuencia->ruta->origen ?? 'Ambato' }} - {{ $boleto->frecuencia->ruta->destino ?? 'Destino' }}
- **Fecha y Hora:** {{ $boleto->frecuencia->fecha_salida ?? 'Fecha programada' }}
- **Asiento N°:** {{ $boleto->numero_asiento }}
- **Total Pagado:** ${{ number_format($boleto->precio_final, 2) }}
</x-mail::panel>

Recuerda presentarte al menos 30 minutos antes de la hora de salida con tu documento de identidad.

<x-mail::button :url="config('app.url')" color="error">
Ver Políticas de Viaje
</x-mail::button>

¡Buen viaje!<br>
**Cooperativa de Transportes Ambato**
</x-mail::message>

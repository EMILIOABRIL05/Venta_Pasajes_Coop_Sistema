<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Boleto de Viaje</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F3F4F6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1F2937;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F3F4F6; padding: 40px 0; width: 100%;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #FFFFFF; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #003366; padding: 30px; text-align: center;">
                            <h1 style="color: #FFFFFF; margin: 0; font-size: 24px; font-weight: bold;">Cooperativa Ambato</h1>
                            <p style="color: #E5E7EB; margin: 5px 0 0 0; font-size: 14px;">¡Tu viaje está confirmado!</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin-top: 0; color: #003366; font-size: 20px;">Hola, {{ $boleto->pasajero->nombre_completo }}</h2>
                            <p style="line-height: 1.6; font-size: 16px; margin-bottom: 25px;">
                                Gracias por confiar en Cooperativa de Transportes Ambato. Adjunto a este correo encontrarás tu boleto oficial en formato PDF.
                            </p>
                            
                            <div style="background-color: #F9FAFB; border-left: 4px solid #CC0000; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
                                <p style="margin: 0 0 12px 0; color: #003366; font-size: 18px;"><strong>Detalles de tu Viaje</strong></p>
                                <p style="margin: 8px 0; font-size: 15px;"><strong>Ruta:</strong> {{ optional(optional($boleto->frecuencia)->ruta)->origen->nombre ?? 'N/A' }} a {{ optional(optional($boleto->frecuencia)->ruta)->destino->nombre ?? 'N/A' }}</p>
                                <p style="margin: 8px 0; font-size: 15px;"><strong>Asiento:</strong> {{ $boleto->numero_asiento }}</p>
                                <p style="margin: 8px 0; font-size: 15px;"><strong>Código de Reserva:</strong> {{ $boleto->codigo_reserva }}</p>
                            </div>

                            <p style="line-height: 1.6; font-size: 16px; margin-top: 25px;">
                                Por favor, presenta el PDF adjunto al momento de abordar el bus. Puedes mostrar el código QR directamente desde la pantalla de tu celular. Te recomendamos llegar con al menos 15 minutos de anticipación.
                            </p>

                            <div style="text-align: center; margin-top: 40px;">
                                <span style="background-color: #CC0000; color: #FFFFFF; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 16px;">¡Buen Viaje!</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #1F2937; padding: 20px; text-align: center;">
                            <p style="color: #F3F4F6; margin: 0; font-size: 12px;">
                                &copy; {{ date('Y') }} Cooperativa de Transportes Ambato. Todos los derechos reservados.
                            </p>
                            <p style="color: #9CA3AF; margin: 8px 0 0 0; font-size: 11px;">
                                Este es un correo generado automáticamente. Por favor no respondas a esta dirección.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

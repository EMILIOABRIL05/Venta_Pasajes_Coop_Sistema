<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta #{{ $venta->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .boleto {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .titulo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }
        .datos {
            margin-bottom: 20px;
        }
        .dato {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .valor {
            color: #333;
        }
        .qr {
            text-align: center;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .page-break {
            page-break-after: always;
        }
        .comprobante-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #ccc;
        }
        .comprobante-header h2 {
            margin: 0;
            font-size: 16px;
            color: #666;
        }
        .comprobante-header span {
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    @foreach($boletos as $index => $boleto)
        <div class="boleto">
            <div class="comprobante-header">
                <h2>Comprobante de Venta #{{ $venta->id }}</h2>
                <span>Boleto {{ $index + 1 }} de {{ $boletos->count() }} &middot; {{ $venta->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="header">
                <div class="titulo">Cooperativa de Transporte</div>
                <div>Boleto de Pasaje</div>
            </div>

            <div class="datos">
                <div class="dato">
                    <span class="label">Pasajero:</span>
                    <span class="valor">{{ $boleto->pasajero->nombre_completo }}</span>
                </div>
                <div class="dato">
                    <span class="label">Asiento:</span>
                    <span class="valor">{{ $boleto->numero_asiento }}</span>
                </div>
                <div class="dato">
                    <span class="label">Ruta:</span>
                    <span class="valor">
                        @if($boleto->frecuencia && $boleto->frecuencia->ruta)
                            {{ $boleto->frecuencia->ruta->origen->nombre }} - {{ $boleto->frecuencia->ruta->destino->nombre }}
                        @else
                            Ruta no disponible
                        @endif
                    </span>
                </div>
                <div class="dato">
                    <span class="label">Precio:</span>
                    <span class="valor">${{ number_format($boleto->precio_final, 2) }}</span>
                </div>
                <div class="dato">
                    <span class="label">Fecha de Emisión:</span>
                    <span class="valor">{{ $boleto->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="qr">
                <img src="data:image/svg+xml;base64,{{ $boleto->qrCode }}" alt="Código QR de Validación" style="width: 150px; height: 150px;">
            </div>

            <div class="footer">
                <p>Este boleto es válido únicamente con el código QR. Presente este documento al abordar.</p>
                <p>ID del Boleto: {{ $boleto->id }}</p>
            </div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>

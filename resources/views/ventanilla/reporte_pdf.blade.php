<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cierre de Turno</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 12px; 
            color: #333; 
            margin: 0; 
            padding: 20px; 
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #4f46e5; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 22px; 
            color: #1e1b4b; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .info-box { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        .info-box td { 
            border: 1px solid #e5e7eb; 
            padding: 10px; 
        }
        .info-box .label { 
            font-weight: bold; 
            background-color: #f8fafc; 
            color: #475569;
            width: 20%; 
        }
        .section-title {
            font-size: 16px; 
            color: #1e293b;
            border-bottom: 1px solid #cbd5e1; 
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .table-data { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        .table-data th { 
            background-color: #4f46e5; 
            color: white; 
            padding: 10px; 
            text-align: left; 
            font-size: 11px;
            text-transform: uppercase;
        }
        .table-data td { 
            border: 1px solid #e5e7eb; 
            padding: 10px; 
        }
        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .totals { 
            width: 45%; 
            float: right; 
            border-collapse: collapse; 
        }
        .totals th { 
            text-align: right; 
            padding: 10px; 
            background-color: #f8fafc; 
            border: 1px solid #e5e7eb; 
            color: #475569;
        }
        .totals td { 
            text-align: right; 
            padding: 10px; 
            border: 1px solid #e5e7eb; 
            font-weight: bold; 
            font-size: 14px;
        }
        .totals .net-income {
            color: #059669;
            font-size: 16px;
        }
        .clear { 
            clear: both; 
        }
        .signature-section {
            margin-top: 100px; 
            text-align: center;
        }
        .signature-line {
            width: 250px; 
            border-top: 1px solid #000; 
            margin: 0 auto; 
            padding-top: 8px;
            font-weight: bold;
            color: #334155;
        }
        .footer { 
            text-align: center; 
            font-size: 10px; 
            color: #9ca3af; 
            margin-top: 50px; 
            border-top: 1px solid #e5e7eb; 
            padding-top: 15px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cooperativa de Transportes Ambato</h1>
        <p>Reporte Oficial de Cierre de Turno</p>
    </div>

    <table class="info-box">
        <tr>
            <td class="label">Cajero:</td>
            <td>{{ $cajero->name }}</td>
            <td class="label">Fecha del Turno:</td>
            <td>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Generado el:</td>
            <td>{{ now()->format('d/m/Y H:i:s') }}</td>
            <td class="label">Total Boletos Emitidos:</td>
            <td>{{ $totalBoletos }}</td>
        </tr>
    </table>

    <div class="section-title">Desglose de Recaudación por Rutas</div>
    <table class="table-data">
        <thead>
            <tr>
                <th>Ruta (Origen → Destino)</th>
                <th style="text-align: center; width: 20%;">Boletos Emitidos</th>
                <th style="text-align: right; width: 25%;">Recaudado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recaudacionPorRuta as $ruta)
                <tr>
                    <td style="font-weight: bold; color: #334155;">{{ $ruta['ruta'] }}</td>
                    <td style="text-align: center;">{{ $ruta['boletos_count'] }}</td>
                    <td style="text-align: right;">${{ number_format($ruta['total_recaudado'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #64748b;">
                        No se registraron ventas en este turno.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <th>Ingreso Bruto:</th>
            <td>${{ number_format($totalBruto, 2) }}</td>
        </tr>
        <tr>
            <th>Reembolsos Aprobados:</th>
            <td style="color: #dc2626;">- ${{ number_format($totalReembolsos, 2) }}</td>
        </tr>
        <tr>
            <th>Ingreso Neto Final:</th>
            <td class="net-income">${{ number_format($totalNeto, 2) }}</td>
        </tr>
    </table>
    
    <div class="clear"></div>

    <div class="signature-section">
        <div class="signature-line">
            Firma del Cajero<br>
            <span style="font-weight: normal; font-size: 11px;">{{ $cajero->name }}</span>
        </div>
    </div>

    <div class="footer">
        Documento generado automáticamente por el Sistema de Ventas • Cooperativa Ambato<br>
        Las cifras expresadas en este reporte están en Dólares Estadounidenses (USD).
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cierre de Turno</title>
    <style>
        /* ── Reset y base ─────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            background: #ffffff;
            padding: 0;
        }

        /* ── Marca de agua diagonal ──────────────────────────────── */
        .watermark {
            position: fixed;
            top: 38%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 62pt;
            font-weight: bold;
            color: rgba(0, 51, 102, 0.04);
            transform: rotate(-35deg);
            z-index: 0;
        }

        /* ── Contenedor principal ─────────────────────────────────── */
        .page {
            position: relative;
            width: 100%;
            padding: 18mm 18mm 20mm 18mm;
            z-index: 1;
        }

        /* ── Cabecera institucional ───────────────────────────────── */
        .header-bar {
            background-color: #003366;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 4px 4px 0 0;
        }
        .header-bar table { width: 100%; border-collapse: collapse; }
        .header-bar td { vertical-align: middle; }
        .org-name {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .org-sub {
            font-size: 8pt;
            color: #93c5fd;
            margin-top: 2px;
        }
        .doc-label {
            text-align: right;
            font-size: 8pt;
            color: #bfdbfe;
        }
        .doc-label strong {
            display: block;
            font-size: 12pt;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        /* ── Banda roja bajo el header ────────────────────────────── */
        .accent-bar {
            background-color: #CC0000;
            height: 4px;
            width: 100%;
        }

        /* ── Metadata del documento ───────────────────────────────── */
        .doc-meta {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 8px 16px;
            font-size: 8pt;
            color: #64748b;
        }
        .doc-meta table { width: 100%; border-collapse: collapse; }
        .doc-meta td { padding: 2px 4px; }
        .doc-meta .meta-label { font-weight: bold; color: #475569; width: 120px; }

        /* ── Sección títulos ─────────────────────────────────────── */
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #003366;
            border-bottom: 2px solid #003366;
            padding-bottom: 3px;
            margin: 14px 0 8px 0;
        }

        /* ── Tarjetas KPI ─────────────────────────────────────────── */
        .kpi-grid { width: 100%; border-collapse: separate; border-spacing: 6px; }
        .kpi-cell {
            width: 25%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
            vertical-align: top;
        }
        .kpi-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            display: block;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 14pt;
            font-weight: bold;
            color: #1e293b;
        }
        .kpi-value.green  { color: #16a34a; }
        .kpi-value.blue   { color: #003366; }
        .kpi-value.red    { color: #CC0000; }
        .kpi-value.violet { color: #7c3aed; }
        .kpi-sub {
            font-size: 7pt;
            color: #94a3b8;
            display: block;
            margin-top: 3px;
        }

        /* ── Tablas de datos ──────────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
        }
        .data-table thead tr {
            background-color: #003366;
            color: #ffffff;
        }
        .data-table thead th {
            padding: 6px 8px;
            text-align: left;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        .data-table thead th.right { text-align: right; }
        .data-table thead th.center { text-align: center; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tbody tr:nth-child(odd)  { background-color: #ffffff; }
        .data-table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tbody td.right  { text-align: right; }
        .data-table tbody td.center { text-align: center; }
        .data-table tfoot tr { background-color: #1e3a5f; }
        .data-table tfoot td {
            padding: 6px 8px;
            color: #ffffff;
            font-weight: bold;
            font-size: 9pt;
        }
        .data-table tfoot td.right { text-align: right; }

        /* Badge de boletos */
        .badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1d4ed8;
            border-radius: 10px;
            padding: 1px 8px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        /* ── Resumen financiero final ─────────────────────────────── */
        .financial-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .financial-box td { padding: 5px 12px; vertical-align: middle; }
        .fin-row-bruto   { background-color: #eff6ff; border: 1px solid #bfdbfe; }
        .fin-row-refund  { background-color: #fff1f2; border: 1px solid #fecaca; }
        .fin-row-neto    { background-color: #003366; color: #ffffff; }
        .fin-label { font-size: 8.5pt; font-weight: bold; }
        .fin-value { font-size: 12pt; font-weight: bold; text-align: right; }
        .fin-sub   { font-size: 7pt; color: #94a3b8; }
        .fin-sub-white { font-size: 7pt; color: #93c5fd; }

        /* ── Bloque de firmas ─────────────────────────────────────── */
        .firma-section { margin-top: 24px; }
        .firma-grid { width: 100%; border-collapse: collapse; }
        .firma-cell {
            width: 33.33%;
            text-align: center;
            padding: 0 14px;
            vertical-align: bottom;
        }
        .firma-line {
            border-top: 1.5px solid #1e293b;
            margin: 0 auto 4px auto;
            width: 85%;
        }
        .firma-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
        }
        .firma-role {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .firma-cedula {
            font-size: 7pt;
            color: #94a3b8;
        }
        .firma-placeholder {
            height: 36px;
        }

        /* ── Pie de página ────────────────────────────────────────── */
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 7pt;
            color: #94a3b8;
            text-align: center;
        }
        .footer strong { color: #64748b; }

        /* Estado del cierre */
        .estado-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
        }
        .estado-cerrado  { background-color: #dcfce7; color: #15803d; }
        .estado-pendiente { background-color: #fef9c3; color: #b45309; }
    </style>
</head>
<body>

<div class="watermark">COOPERATIVA AMBATO</div>

<div class="page">

    {{-- ── Cabecera institucional ─────────────────────────────────── --}}
    <div class="header-bar">
        <table>
            <tr>
                <td>
                    <div class="org-name">Cooperativa de Transportes Ambato</div>
                    <div class="org-sub">Sistema de Gestión de Pasajes — Módulo Ventanilla</div>
                </td>
                <td>
                    <div class="doc-label">
                        <span>Reporte de Cierre de Turno</span>
                        <strong>Nº {{ str_pad($cierreExistente?->id ?? 'S/N', 6, '0', STR_PAD_LEFT) }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-bar"></div>

    {{-- ── Metadata del documento ──────────────────────────────────── --}}
    <div class="doc-meta">
        <table>
            <tr>
                <td><span class="meta-label">Fecha del turno:</span></td>
                <td>{{ \Carbon\Carbon::parse($fecha)->format('d \d\e F \d\e Y') }}</td>
                <td><span class="meta-label">Cajero responsable:</span></td>
                <td>{{ $cajero->name }}</td>
            </tr>
            <tr>
                <td><span class="meta-label">Generado el:</span></td>
                <td>{{ now()->format('d/m/Y H:i:s') }}</td>
                <td><span class="meta-label">Estado del turno:</span></td>
                <td>
                    @if($cierreExistente)
                        <span class="estado-badge estado-cerrado">✔ CERRADO — {{ $cierreExistente->created_at->format('H:i') }}</span>
                    @else
                        <span class="estado-badge estado-pendiente">⚑ EN CURSO (sin cierre oficial)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><span class="meta-label">Correo cajero:</span></td>
                <td>{{ $cajero->email }}</td>
                <td><span class="meta-label">Ref. sistema:</span></td>
                <td style="font-family: monospace; font-size: 7.5pt; color: #64748b;">
                    COOP-TRN-{{ strtoupper(\Illuminate\Support\Str::random(8)) }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ── KPIs ────────────────────────────────────────────────────── --}}
    <div class="section-title">Indicadores del Turno</div>
    <table class="kpi-grid">
        <tr>
            <td class="kpi-cell">
                <span class="kpi-label">Total Cobrado</span>
                <span class="kpi-value blue">${{ number_format($totalBruto, 2) }}</span>
                <span class="kpi-sub">Ingreso bruto</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Ingreso Neto</span>
                <span class="kpi-value green">${{ number_format($totalNeto, 2) }}</span>
                <span class="kpi-sub">Bruto − reembolsos</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Boletos Vendidos</span>
                <span class="kpi-value blue">{{ number_format($totalBoletos) }}</span>
                <span class="kpi-sub">Unidades emitidas</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Promedio por Boleto</span>
                <span class="kpi-value violet">${{ number_format($promedioPorBoleto, 2) }}</span>
                <span class="kpi-sub">Precio promedio</span>
            </td>
        </tr>
    </table>

    {{-- ── Resumen financiero ───────────────────────────────────────── --}}
    <div class="section-title">Resumen Financiero</div>
    <table class="financial-box">
        <tr class="fin-row-bruto">
            <td class="fin-label">Ingresos Brutos (ventas del turno)</td>
            <td class="fin-value" style="color: #1d4ed8;">${{ number_format($totalBruto, 2) }}</td>
        </tr>
        <tr class="fin-row-refund">
            <td class="fin-label" style="color: #b91c1c;">
                (−) Reembolsos aprobados
                <span style="font-size:7pt; font-weight:normal; color:#94a3b8;">
                    — Solo reembolsos con estado "aprobado" generados sobre ventas de este cajero
                </span>
            </td>
            <td class="fin-value" style="color: #CC0000;">−${{ number_format($totalReembolsos, 2) }}</td>
        </tr>
        <tr class="fin-row-neto">
            <td class="fin-label" style="color:#fff;">
                INGRESO NETO TOTAL
                <span class="fin-sub-white">Monto a entregar / depositar</span>
            </td>
            <td class="fin-value" style="color:#6ee7b7;">${{ number_format($totalNeto, 2) }}</td>
        </tr>
    </table>

    {{-- ── Desglose por Ruta ────────────────────────────────────────── --}}
    <div class="section-title">Desglose por Ruta</div>
    @if($recaudacionPorRuta->isEmpty())
        <p style="font-size:8.5pt; color:#94a3b8; text-align:center; padding: 14px 0;">
            Sin ventas registradas para este turno.
        </p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:28px;">#</th>
                    <th>Ruta</th>
                    <th class="center" style="width:70px;">Boletos</th>
                    <th class="right"  style="width:110px;">Recaudado</th>
                    <th class="right"  style="width:80px;">Contribución</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recaudacionPorRuta as $i => $item)
                    @php $pct = $totalBruto > 0 ? round(($item['total_recaudado'] / $totalBruto) * 100, 1) : 0; @endphp
                    <tr>
                        <td style="color:#94a3b8; font-size:7.5pt;">{{ $i + 1 }}</td>
                        <td>{{ $item['ruta'] }}</td>
                        <td class="center"><span class="badge">{{ $item['boletos_count'] }}</span></td>
                        <td class="right" style="font-weight:bold;">${{ number_format($item['total_recaudado'], 2) }}</td>
                        <td class="right" style="color:#64748b;">{{ $pct }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL GENERAL</td>
                    <td class="right">{{ $totalBoletos }}</td>
                    <td class="right">${{ number_format($totalBruto, 2) }}</td>
                    <td class="right">100%</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Últimas transacciones ────────────────────────────────────── --}}
    <div class="section-title">Detalle de Transacciones del Turno</div>
    @if($ultimasTransacciones->isEmpty())
        <p style="font-size:8.5pt; color:#94a3b8; text-align:center; padding: 14px 0;">
            No hay transacciones registradas.
        </p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;">Hora</th>
                    <th style="width:55px;">Venta #</th>
                    <th>Pasajero</th>
                    <th>Ruta</th>
                    <th class="center" style="width:55px;">Boletos</th>
                    <th class="right"  style="width:90px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ultimasTransacciones as $tx)
                    <tr>
                        <td style="font-family:monospace; font-size:8pt;">{{ $tx['hora'] }}</td>
                        <td style="font-family:monospace; font-size:7.5pt; color:#64748b;">#{{ $tx['id'] }}</td>
                        <td>{{ $tx['pasajero'] }}</td>
                        <td style="font-size:8pt;">{{ $tx['ruta'] }}</td>
                        <td class="center"><span class="badge">{{ $tx['boletos'] }}</span></td>
                        <td class="right" style="font-weight:bold;">${{ number_format($tx['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── Bloque de firmas de responsabilidad ─────────────────────── --}}
    <div class="firma-section">
        <div class="section-title">Firmas de Responsabilidad</div>
        <table class="firma-grid">
            <tr>
                {{-- Firma 1: Cajero --}}
                <td class="firma-cell">
                    <div class="firma-placeholder"></div>
                    <div class="firma-line"></div>
                    <div class="firma-name">{{ $cajero->name }}</div>
                    <div class="firma-role">Cajero / Ventanilla</div>
                    <div class="firma-cedula">Responsable del turno</div>
                </td>

                {{-- Firma 2: Supervisor --}}
                <td class="firma-cell">
                    <div class="firma-placeholder"></div>
                    <div class="firma-line"></div>
                    <div class="firma-name">______________________________</div>
                    <div class="firma-role">Supervisor de Turno</div>
                    <div class="firma-cedula">Firma y sello</div>
                </td>

                {{-- Firma 3: Contabilidad --}}
                <td class="firma-cell">
                    <div class="firma-placeholder"></div>
                    <div class="firma-line"></div>
                    <div class="firma-name">______________________________</div>
                    <div class="firma-role">Departamento de Contabilidad</div>
                    <div class="firma-cedula">Revisado y conforme</div>
                </td>
            </tr>
        </table>

        {{-- Declaración legal --}}
        <p style="margin-top:14px; font-size:7pt; color:#94a3b8; text-align:justify; line-height:1.5;">
            Declaro que los datos contenidos en este comprobante de cierre de turno son verídicos y
            corresponden fielmente a las operaciones de venta de pasajes realizadas durante la jornada
            indicada. El presente documento tiene carácter oficial y constituye un instrumento contable
            válido para los efectos de auditoría interna de la Cooperativa de Transportes Ambato.
        </p>
    </div>

    {{-- ── Pie de página ────────────────────────────────────────────── --}}
    <div class="footer">
        <strong>Cooperativa de Transportes Ambato</strong> · Sistema de Venta de Pasajes v0.7.0 ·
        Generado el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }} ·
        Documento de uso interno — confidencial
    </div>

</div>
</body>
</html>

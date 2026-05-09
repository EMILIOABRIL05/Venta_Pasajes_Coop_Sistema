<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CierreTurnoExport implements WithMultipleSheets
{
    public function __construct(
        private readonly int    $userId,
        private readonly string $fecha,
        private readonly string $cajeroNombre,
    ) {}

    /**
     * Genera dos hojas: Transacciones + Resumen por Ruta.
     */
    public function sheets(): array
    {
        // ── Carga única de ventas compartida por ambas hojas ─────────────────
        $ventas = Venta::with([
                'boletos.frecuencia.ruta.origen',
                'boletos.frecuencia.ruta.destino',
                'boletos.pasajero',
                'reembolsos',
            ])
            ->where('user_id', $this->userId)
            ->whereDate('created_at', $this->fecha)
            ->orderBy('created_at')
            ->get();

        return [
            new TransaccionesSheet($ventas, $this->fecha, $this->cajeroNombre),
            new ResumenRutaSheet($ventas, $this->fecha, $this->cajeroNombre),
        ];
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Hoja 1: Detalle de Transacciones
// ═══════════════════════════════════════════════════════════════════════════════

class TransaccionesSheet implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private readonly \Illuminate\Support\Collection $ventas,
        private readonly string $fecha,
        private readonly string $cajeroNombre,
    ) {}

    public function title(): string { return 'Transacciones'; }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->ventas;
    }

    public function headings(): array
    {
        return [
            // Fila 1: título del reporte (se mergeará vía styles)
            ['COOPERATIVA DE TRANSPORTES AMBATO', '', '', '', '', '', '', ''],
            // Fila 2: subtítulo
            [
                'Reporte de Cierre de Turno — ' . \Carbon\Carbon::parse($this->fecha)->format('d/m/Y'),
                '', '', '', '', '', '', '',
            ],
            // Fila 3: info cajero
            ['Cajero: ' . $this->cajeroNombre, '', '', '', '', '', '', ''],
            // Fila 4: vacía
            ['', '', '', '', '', '', '', ''],
            // Fila 5: cabeceras reales
            [
                'Hora', 'Venta #', 'Pasajero', 'Cédula',
                'Ruta', 'Asientos', 'Boletos', 'Total ($)',
            ],
        ];
    }

    public function map($venta): array
    {
        $primerBoleto = $venta->boletos->first();
        $ruta = $primerBoleto?->frecuencia?->ruta
            ? (optional($primerBoleto->frecuencia->ruta->origen)->nombre ?? '—')
              . ' → '
              . (optional($primerBoleto->frecuencia->ruta->destino)->nombre ?? '—')
            : 'Sin ruta';

        return [
            $venta->created_at->format('H:i'),
            $venta->id,
            optional($primerBoleto?->pasajero)->nombre_completo ?? '—',
            optional($primerBoleto?->pasajero)->cedula ?? '—',
            $ruta,
            $venta->boletos->pluck('numero_asiento')->sort()->join(', '),
            $venta->boletos->count(),
            number_format((float) $venta->total, 2, '.', ''),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->ventas->count() + 5; // 4 header rows + 1 heading row

        // Merge celdas para las filas de título
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        // Fila de totales al final
        $totalRow = $lastRow + 1;
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->setCellValue("G{$totalRow}", $this->ventas->sum(fn ($v) => $v->boletos->count()));
        $sheet->setCellValue(
            "H{$totalRow}",
            number_format((float) $this->ventas->sum('total'), 2, '.', '')
        );

        return [
            // Fila 1: título grande
            1 => [
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Fila 2: subtítulo
            2 => [
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Fila 3: cajero
            3 => [
                'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a4a7a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Fila 5: cabeceras
            5 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CC0000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Fila de totales
            $totalRow => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '003366']],
                ],
            ],
            // Rango de datos: bordes finos
            "A6:H{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ],
        ];
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Hoja 2: Resumen por Ruta
// ═══════════════════════════════════════════════════════════════════════════════

class ResumenRutaSheet implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithTitle, ShouldAutoSize
{
    private \Illuminate\Support\Collection $porRuta;
    private float  $totalBruto;
    private float  $totalReembolsos;
    private int    $totalBoletos;

    public function __construct(
        private readonly \Illuminate\Support\Collection $ventas,
        private readonly string $fecha,
        private readonly string $cajeroNombre,
    ) {
        $this->totalBruto      = (float) $ventas->sum('total');
        $this->totalBoletos    = (int)   $ventas->sum(fn ($v) => $v->boletos->count());
        $this->totalReembolsos = (float) $ventas->sum(
            fn ($v) => $v->reembolsos->where('estado', 'aprobado')->sum('monto')
        );

        $this->porRuta = $ventas
            ->flatMap(fn ($venta) =>
                $venta->boletos->map(fn ($b) => [
                    'ruta_id'     => optional(optional($b->frecuencia)->ruta)->id ?? 0,
                    'ruta_nombre' => $b->frecuencia && $b->frecuencia->ruta
                        ? (optional($b->frecuencia->ruta->origen)->nombre ?? '—')
                          . ' → '
                          . (optional($b->frecuencia->ruta->destino)->nombre ?? '—')
                        : 'Sin ruta',
                    'total'       => (float) $venta->total,
                ])
            )
            ->groupBy('ruta_id')
            ->map(fn ($g) => [
                'ruta'     => $g->first()['ruta_nombre'],
                'boletos'  => $g->count(),
                'total'    => round($g->sum('total'), 2),
                'pct'      => $this->totalBruto > 0
                    ? round(($g->sum('total') / $this->totalBruto) * 100, 1)
                    : 0,
            ])
            ->values();
    }

    public function title(): string { return 'Resumen por Ruta'; }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->porRuta;
    }

    public function headings(): array
    {
        return [
            ['COOPERATIVA DE TRANSPORTES AMBATO', '', '', '', ''],
            ['Resumen por Ruta — ' . \Carbon\Carbon::parse($this->fecha)->format('d/m/Y'), '', '', '', ''],
            ['Cajero: ' . $this->cajeroNombre, '', '', '', ''],
            ['', '', '', '', ''],
            ['#', 'Ruta', 'Boletos Vendidos', 'Recaudado ($)', '% Contribución'],
        ];
    }

    public function map($item): array
    {
        static $idx = 0;
        $idx++;
        return [
            $idx,
            $item['ruta'],
            $item['boletos'],
            number_format($item['total'], 2, '.', ''),
            $item['pct'] . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow  = $this->porRuta->count() + 5;
        $totalRow = $lastRow + 1;
        $kpiStart = $totalRow + 2;

        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');

        // Bloque KPI financiero al final
        $sheet->setCellValue("A{$totalRow}", 'SUBTOTAL');
        $sheet->setCellValue("C{$totalRow}", $this->totalBoletos);
        $sheet->setCellValue("D{$totalRow}", number_format($this->totalBruto, 2, '.', ''));
        $sheet->setCellValue("E{$totalRow}", '100%');

        $sheet->setCellValue("A{$kpiStart}",     'INGRESO BRUTO');
        $sheet->setCellValue("B{$kpiStart}",     '$' . number_format($this->totalBruto, 2));
        $sheet->setCellValue("A" . ($kpiStart+1), 'REEMBOLSOS APROBADOS');
        $sheet->setCellValue("B" . ($kpiStart+1), '-$' . number_format($this->totalReembolsos, 2));
        $sheet->setCellValue("A" . ($kpiStart+2), 'INGRESO NETO');
        $sheet->setCellValue("B" . ($kpiStart+2), '$' . number_format($this->totalBruto - $this->totalReembolsos, 2));

        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['italic' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a4a7a']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CC0000']]],
            $totalRow        => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']]],
            $kpiStart        => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']]],
            ($kpiStart + 1)  => ['font' => ['color' => ['rgb' => 'CC0000']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF1F2']]],
            ($kpiStart + 2)  => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']]],
        ];
    }
}

<?php

namespace App\Services;

use App\Models\CierreTurno;
use App\Models\Venta;
use Illuminate\Support\Collection;

/**
 * CierreTurnoService — Single Responsibility: cálculos del turno de caja.
 *
 * Principios aplicados:
 *  - SRP: única responsabilidad → calcular métricas del turno.
 *  - OCP: abierto a extensión (nuevas métricas) sin modificar el controlador.
 *  - DIP: el controlador depende de esta abstracción, no de Eloquent directamente.
 *
 * Todos los métodos son puros (no mutan estado externo) y trabajan
 * sobre la colección de ventas ya hidratada, evitando N+1 queries.
 */
class CierreTurnoService
{
    // ─── Relaciones Eager-Load requeridas ─────────────────────────────────────
    public const RELATIONS = [
        'boletos.frecuencia.ruta.origen',
        'boletos.frecuencia.ruta.destino',
        'boletos.pasajero',
        'reembolsos',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Punto de entrada principal: devuelve TODO lo necesario para el dashboard,
    // el PDF y el Excel en una única llamada.
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Construye el resumen completo del turno para un cajero y fecha dados.
     *
     * @param  int     $userId  ID del cajero autenticado
     * @param  string  $fecha   Fecha en formato Y-m-d
     * @return array{
     *   cierreExistente: CierreTurno|null,
     *   ventas: Collection,
     *   totalBruto: float,
     *   totalNeto: float,
     *   totalReembolsos: float,
     *   totalBoletos: int,
     *   promedioPorBoleto: float,
     *   recaudacionPorRuta: Collection,
     *   ultimasTransacciones: Collection,
     *   chartHorario: array,
     * }
     */
    public function resumenCompleto(int $userId, string $fecha): array
    {
        $cierreExistente = $this->obtenerCierre($userId, $fecha);
        $ventas          = $this->cargarVentas($userId, $fecha);

        $totalBruto      = $this->calcularBruto($ventas);
        $totalBoletos    = $this->contarBoletos($ventas);
        $totalReembolsos = $this->calcularReembolsos($ventas);
        $totalNeto       = round($totalBruto - $totalReembolsos, 2);

        return [
            'cierreExistente'      => $cierreExistente,
            'ventas'               => $ventas,
            'totalBruto'           => $totalBruto,
            'totalNeto'            => $totalNeto,
            'totalReembolsos'      => $totalReembolsos,
            'totalBoletos'         => $totalBoletos,
            'promedioPorBoleto'    => $this->calcularPromedio($totalBruto, $totalBoletos),
            'recaudacionPorRuta'   => $this->agruparPorRuta($ventas),
            'ultimasTransacciones' => $this->ultimasTransacciones($ventas),
            'chartHorario'         => $this->chartHorario($ventas),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Métodos de consulta
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Recupera el registro de cierre del día, o null si no existe.
     */
    public function obtenerCierre(int $userId, string $fecha): ?CierreTurno
    {
        return CierreTurno::where('user_id', $userId)
            ->where('fecha', $fecha)
            ->first();
    }

    /**
     * Carga las ventas del cajero para la fecha indicada con eager-load completo.
     * La query filtra por user_id → aislamiento de datos garantizado en BD.
     */
    public function cargarVentas(int $userId, string $fecha): Collection
    {
        return Venta::with(self::RELATIONS)
            ->where('user_id', $userId)          // ← restricción de propietario
            ->whereDate('created_at', $fecha)
            ->orderBy('created_at')
            ->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Métricas KPI — todos trabajan sobre la Collection en memoria
    // ──────────────────────────────────────────────────────────────────────────

    public function calcularBruto(Collection $ventas): float
    {
        return round((float) $ventas->sum('total'), 2);
    }

    public function contarBoletos(Collection $ventas): int
    {
        return (int) $ventas->sum(fn (Venta $v) => $v->boletos->count());
    }

    public function calcularReembolsos(Collection $ventas): float
    {
        return round(
            (float) $ventas->sum(
                fn (Venta $v) => $v->reembolsos->where('estado', 'aprobado')->sum('monto')
            ),
            2
        );
    }

    public function calcularPromedio(float $bruto, int $boletos): float
    {
        return $boletos > 0 ? round($bruto / $boletos, 2) : 0.0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Desglose por ruta
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Agrupa la recaudación por ruta origin→destination.
     *
     * @return Collection<int, array{ruta: string, total_recaudado: float, boletos_count: int}>
     */
    public function agruparPorRuta(Collection $ventas): Collection
    {
        return $ventas
            ->flatMap(fn (Venta $venta) =>
                $venta->boletos->map(fn ($b) => [
                    'ruta_id'     => optional(optional($b->frecuencia)->ruta)->id ?? 0,
                    'ruta_nombre' => $this->nombreRuta($b),
                    'venta_total' => (float) $venta->total,
                ])
            )
            ->groupBy('ruta_id')
            ->map(fn ($grupo) => [
                'ruta'            => $grupo->first()['ruta_nombre'],
                'total_recaudado' => round($grupo->sum('venta_total'), 2),
                'boletos_count'   => $grupo->count(),
            ])
            ->values();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Tabla de últimas transacciones
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Devuelve las últimas N ventas como array plano para la vista / PDF.
     *
     * @return Collection<int, array{id, hora, total, boletos, asientos, pasajero, ruta}>
     */
    public function ultimasTransacciones(Collection $ventas, int $limite = 10): Collection
    {
        return $ventas
            ->sortByDesc('created_at')
            ->take($limite)
            ->map(fn (Venta $v) => [
                'id'       => $v->id,
                'hora'     => $v->created_at->format('H:i'),
                'total'    => (float) $v->total,
                'boletos'  => $v->boletos->count(),
                'asientos' => $v->boletos->pluck('numero_asiento')->sort()->join(', '),
                'pasajero' => optional($v->boletos->first()?->pasajero)->nombre_completo ?? '—',
                'ruta'     => $this->nombreRutaVenta($v),
            ])
            ->values();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Datos para Chart.js — progresión horaria
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Construye 24 slots horarios (00:00–23:00) con totales acumulados.
     *
     * @return array{labels: string[], totales: float[], boletos: int[]}
     */
    public function chartHorario(Collection $ventas): array
    {
        $slots = collect(range(0, 23))->mapWithKeys(fn ($h) => [
            $h => [
                'label'   => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'total'   => 0.0,
                'boletos' => 0,
            ],
        ]);

        foreach ($ventas as $v) {
            $hora = (int) $v->created_at->format('G');
            $slots[$hora]['total']   += (float) $v->total;
            $slots[$hora]['boletos'] += $v->boletos->count();
        }

        return [
            'labels'  => $slots->pluck('label')->values()->toArray(),
            'totales' => $slots->pluck('total')->map(fn ($t) => round($t, 2))->values()->toArray(),
            'boletos' => $slots->pluck('boletos')->values()->toArray(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    /** Resuelve "Origen → Destino" de un boleto de forma segura. */
    private function nombreRuta($boleto): string
    {
        if (!$boleto->frecuencia || !$boleto->frecuencia->ruta) {
            return 'Sin ruta';
        }

        $origen  = optional($boleto->frecuencia->ruta->origen)->nombre  ?? '—';
        $destino = optional($boleto->frecuencia->ruta->destino)->nombre ?? '—';

        return "{$origen} → {$destino}";
    }

    /** Resuelve la ruta desde el primer boleto de una venta. */
    private function nombreRutaVenta(Venta $venta): string
    {
        $primerBoleto = $venta->boletos->first();

        return $primerBoleto ? $this->nombreRuta($primerBoleto) : 'Sin ruta';
    }
}

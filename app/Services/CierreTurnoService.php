<?php

namespace App\Services;

use App\Models\CierreTurno;
use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        'boletos.viaje',
        'reembolsos',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Punto de entrada principal: devuelve TODO lo necesario para el dashboard,
    // el PDF y el Excel en una única llamada.
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Construye el resumen completo del turno para un cajero y fecha dados.
     *
     * @param  int  $userId  ID del cajero autenticado
     * @param  string  $fecha  Fecha en formato Y-m-d
     * @param  int  $perPage  Elementos por página en la tabla de transacciones
     * @return array{
     *   cierreExistente: CierreTurno|null,
     *   ventas: Collection,
     *   totalBruto: float,
     *   totalNeto: float,
     *   totalReembolsos: float,
     *   totalBoletos: int,
     *   promedioPorBoleto: float,
     *   recaudacionPorRuta: Collection,
     *   ultimasTransacciones: LengthAwarePaginator,
     *   chartHorario: array,
     * }
     */
    public function resumenCompleto(int $userId, string $fecha, int $perPage = 10): array
    {
        $cierreExistente = $this->obtenerCierre($userId, $fecha);
        $ventas = $this->cargarVentas($userId, $fecha);
        $transaccionesPaginadas = $this->cargarTodasVentasPaginadas($userId, $fecha, $perPage);

        $totalBruto = $this->calcularBruto($ventas);
        $totalBoletos = $this->contarBoletos($ventas);
        $totalReembolsos = $this->calcularReembolsos($ventas);
        $totalNeto = round($totalBruto - $totalReembolsos, 2);

        return [
            'cierreExistente' => $cierreExistente,
            'ventas' => $ventas,
            'totalBruto' => $totalBruto,
            'totalNeto' => $totalNeto,
            'totalReembolsos' => $totalReembolsos,
            'totalBoletos' => $totalBoletos,
            'promedioPorBoleto' => $this->calcularPromedio($totalBruto, $totalBoletos),
            'recaudacionPorRuta' => $this->agruparPorRuta($ventas),
            'ultimasTransacciones' => $transaccionesPaginadas,
            'chartHorario' => $this->chartHorario($ventas),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Métodos de consulta
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Recupera el registro de cierre oficial del día, o null si el turno sigue abierto.
     *
     * @param  int  $userId  ID del cajero autenticado
     * @param  string  $fecha  Fecha en formato Y-m-d
     * @return CierreTurno|null Registro persistido o null si no existe
     */
    public function obtenerCierre(int $userId, string $fecha): ?CierreTurno
    {
        return CierreTurno::where('user_id', $userId)
            ->where('fecha', $fecha)
            ->first();
    }

    /**
     * Carga las ventas de ventanilla del cajero para la fecha indicada con eager-load completo.
     *
     * La cláusula `where('user_id', $userId)` garantiza el **aislamiento de datos**:
     * ningún cajero puede acceder a las ventas de otro aunque comparta el mismo turno.
     *
     * CRÍTICO: Solo incluye ventas de canal 'ventanilla'. Las ventas web se excluyen
     * del cierre de caja físico porque el dinero ingresa por vía digital.
     *
     * @param  int  $userId  ID del cajero — restricción de propietario aplicada en BD
     * @param  string  $fecha  Fecha en formato Y-m-d (usa `whereDate` para ignorar la hora)
     * @return Collection<int, Venta> Colección hidratada con relaciones
     */
    public function cargarVentas(int $userId, string $fecha): Collection
    {
        return Venta::with(self::RELATIONS)
            ->where('user_id', $userId)
            ->where('canal_venta', Venta::CANAL_VENTANILLA)
            ->whereDate('created_at', $fecha)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Carga TODAS las ventas (ventanilla + web) del cajero para la fecha indicada, paginadas.
     *
     * Esta consulta se usa únicamente para la tabla de "Últimas Transacciones",
     * mostrando tanto ventas físicas como digitales en un solo historial unificado.
     *
     * - Ventanilla: filtra por user_id = cajero (aislamiento de caja).
     * - Web: incluye TODAS las ventas web del día (sin filtro de usuario),
     *   ya que el cajero necesita visibilidad completa del canal digital.
     *
     * IMPORTANTE: Las métricas financieras (totalBruto, totalNeto, etc.) siguen
     * usando `cargarVentas()` que filtra solo `canal_venta = 'ventanilla'`.
     *
     * @param  int  $userId  ID del cajero
     * @param  string  $fecha  Fecha en formato Y-m-d
     * @param  int  $perPage  Elementos por página
     */
    public function cargarTodasVentasPaginadas(int $userId, string $fecha, int $perPage = 10): LengthAwarePaginator
    {
        return Venta::with(self::RELATIONS)
            ->where(function ($query) use ($userId, $fecha) {
                $query->where(function ($q) use ($userId, $fecha) {
                    $q->where('user_id', $userId)
                        ->where('canal_venta', Venta::CANAL_VENTANILLA)
                        ->whereDate('created_at', $fecha);
                })->orWhere(function ($q) use ($fecha) {
                    $q->where('canal_venta', Venta::CANAL_WEB)
                        ->whereDate('created_at', $fecha);
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Métricas KPI — todos trabajan sobre la Collection en memoria
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Calcula el ingreso bruto como suma de `total` de todas las ventas del turno.
     *
     * @param  Collection  $ventas  Colección de ventas del turno
     * @return float Total bruto redondeado a 2 decimales
     */
    public function calcularBruto(Collection $ventas): float
    {
        return round((float) $ventas->sum('total'), 2);
    }

    /**
     * Cuenta el número total de boletos emitidos en el turno.
     *
     * @param  Collection  $ventas  Colección de ventas del turno
     * @return int Suma de boletos de todas las ventas
     */
    public function contarBoletos(Collection $ventas): int
    {
        return (int) $ventas->sum(fn (Venta $v) => $v->boletos->count());
    }

    /**
     * Suma los montos de reembolsos con estado `aprobado` sobre las ventas del turno.
     *
     * Solo se consideran reembolsos en estado 'aprobado' para respetar el flujo
     * de aprobación definido en el módulo de gestión de reembolsos.
     *
     * @param  Collection  $ventas  Colección de ventas del turno
     * @return float Total de reembolsos aprobados, redondeado a 2 decimales
     */
    public function calcularReembolsos(Collection $ventas): float
    {
        return round(
            (float) $ventas->sum(
                fn (Venta $v) => $v->reembolsos->where('estado', 'aprobado')->sum('monto')
            ),
            2
        );
    }

    /**
     * Calcula el precio promedio por boleto vendido.
     *
     * Evita la división por cero devolviendo 0.0 cuando no hay boletos.
     *
     * @param  float  $bruto  Ingreso bruto del turno
     * @param  int  $boletos  Número de boletos vendidos
     * @return float Promedio redondeado a 2 decimales, o 0.0 si $boletos = 0
     */
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
            ->flatMap(fn (Venta $venta) => $venta->boletos->map(fn ($b) => [
                'ruta_id' => optional(optional($b->frecuencia)->ruta)->id ?? 0,
                'ruta_nombre' => $this->nombreRuta($b),
                'venta_total' => (float) $venta->total,
            ])
            )
            ->groupBy('ruta_id')
            ->map(fn ($grupo) => [
                'ruta' => $grupo->first()['ruta_nombre'],
                'total_recaudado' => round($grupo->sum('venta_total'), 2),
                'boletos_count' => $grupo->count(),
            ])
            ->values();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Tabla de últimas transacciones
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Transforma una colección de ventas en array plano para la vista / PDF.
     *
     * @return Collection<int, array{id, hora, total, boletos, asientos, pasajero, ruta, canal_venta}>
     */
    public function ultimasTransacciones(Collection $ventas, int $limite = 10): Collection
    {
        return $ventas
            ->take($limite)
            ->map(fn (Venta $v) => [
                'id' => $v->id,
                'hora' => $v->created_at->format('H:i'),
                'total' => (float) $v->total,
                'boletos' => $v->boletos->count(),
                'asientos' => $v->boletos->pluck('numero_asiento')->sort()->join(', '),
                'pasajero' => optional($v->boletos->first()?->pasajero)->nombre_completo ?? '—',
                'ruta' => $this->nombreRutaVenta($v),
                'boleto_ids' => $v->boletos->pluck('id')->toArray(),
                'canal_venta' => $v->canal_venta ?? 'ventanilla',
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
                'label' => str_pad($h, 2, '0', STR_PAD_LEFT).':00',
                'total' => 0.0,
                'boletos' => 0,
            ],
        ])->toArray();

        foreach ($ventas as $v) {
            $hora = (int) $v->created_at->format('G');
            $slots[$hora]['total'] += (float) $v->total;
            $slots[$hora]['boletos'] += $v->boletos->count();
        }

        $slotsCollection = collect($slots);

        return [
            'labels' => $slotsCollection->pluck('label')->values()->toArray(),
            'totales' => $slotsCollection->pluck('total')->map(fn ($t) => round($t, 2))->values()->toArray(),
            'boletos' => $slotsCollection->pluck('boletos')->values()->toArray(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    /** Resuelve "Origen → Destino" de un boleto de forma segura. */
    private function nombreRuta($boleto): string
    {
        if (! $boleto->frecuencia || ! $boleto->frecuencia->ruta) {
            return 'Sin ruta';
        }

        $origen = optional($boleto->frecuencia->ruta->origen)->nombre ?? '—';
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

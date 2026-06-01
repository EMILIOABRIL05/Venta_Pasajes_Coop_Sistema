<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use SoftDeletes;

    protected $table = 'buses';

    protected $fillable = [
        'placa',
        'marca_chasis',
        'carroceria',
        'anio',
        'foto',
        'numero_asientos',
        'mapa_asientos',
        'estado',
    ];

    protected $casts = [
        'mapa_asientos' => 'array',
        'estructura_asientos' => 'array',
        'anio' => 'integer',
        'numero_asientos' => 'integer',
    ];

    public static function estructuraAsientosBase(): array
    {
        return [
            'filas' => 10,
            'pasillo' => true,
            'asientos_por_fila' => 4,
            'capacidad_total' => 40,
            'extras' => [],
        ];
    }

    public static function generarEstructuraAsientos(int $filas, bool $tienePasillo = true): array
    {
        $asientosPorFila = self::calcularAsientosPorFila($tienePasillo);

        return [
            'filas' => $filas,
            'pasillo' => $tienePasillo,
            'asientos_por_fila' => $asientosPorFila,
            'capacidad_total' => $filas * $asientosPorFila,
            'extras' => [],
            'version' => 1,
        ];
    }

    public static function generarMapaAsientosCategorizado(
        int $filas,
        bool $tienePasillo,
        ?CategoriaAsiento $categoriaDefault,
        iterable $categorias,
        array $asientosCategoria = []
    ): array {
        $mapa = self::generarEstructuraAsientos($filas, $tienePasillo);
        $capacidad = (int) $mapa['capacidad_total'];

        $categoriaPorAsiento = [];
        foreach ($asientosCategoria as $grupo) {
            foreach (($grupo['asientos'] ?? []) as $asiento) {
                $categoriaPorAsiento[(string) $asiento] = $grupo['categoria_id'] ?? null;
            }
        }

        $mapa['categoria_defecto_id'] = $categoriaDefault?->id;
        $mapa['categorias_asiento'] = collect($categorias)
            ->map(static fn (CategoriaAsiento $categoria) => [
                'id' => $categoria->id,
                'codigo' => $categoria->codigo,
                'nombre' => $categoria->nombre,
                'recargo' => (float) $categoria->recargo,
                'color_hex' => $categoria->color_hex,
                'es_default' => $categoria->es_default,
            ])
            ->values()
            ->all();

        $mapa['asientos'] = collect(range(1, $capacidad))
            ->map(static function (int $numero) use ($categoriaDefault, $categoriaPorAsiento) {
                return [
                    'numero' => (string) $numero,
                    'categoria_id' => $categoriaPorAsiento[(string) $numero] ?? $categoriaDefault?->id,
                ];
            })
            ->values()
            ->all();

        return $mapa;
    }

    private static function calcularAsientosPorFila(bool $tienePasillo): int
    {
        return $tienePasillo ? 4 : 5;
    }

    public static function calcularCapacidad(int $filas, bool $tienePasillo = true): int
    {
        return $filas * self::calcularAsientosPorFila($tienePasillo);
    }

    public function obtenerInfoMapa(): array
    {
        $mapa = $this->mapa_asientos ?? self::estructuraAsientosBase();

        return [
            'filas' => $mapa['filas'] ?? 10,
            'pasillo' => $mapa['pasillo'] ?? true,
            'asientos_por_fila' => $mapa['asientos_por_fila'] ?? self::calcularAsientosPorFila($mapa['pasillo'] ?? true),
            'capacidad_total' => $mapa['capacidad_total'] ?? self::calcularCapacidad($mapa['filas'] ?? 10, $mapa['pasillo'] ?? true),
            'extras' => $mapa['extras'] ?? [],
            'version' => $mapa['version'] ?? 1,
        ];
    }

    public function validarEstructuraAsientos(): bool
    {
        if (! is_array($this->mapa_asientos)) {
            return false;
        }

        $requeridos = ['filas', 'pasillo', 'asientos_por_fila', 'capacidad_total'];
        foreach ($requeridos as $campo) {
            if (! isset($this->mapa_asientos[$campo])) {
                return false;
            }
        }

        return true;
    }

    public function getEstructuraAsientosAttribute(): ?array
    {
        return $this->mapa_asientos;
    }

    public function setEstructuraAsientosAttribute(?array $value): void
    {
        $this->attributes['mapa_asientos'] = is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : null;
    }

    // Scope útil: solo buses disponibles
    public function scopeDisponible($query)
    {
        return $query->whereNotIn('estado', ['mantenimiento', 'en_ruta']);
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }

    public function getMapaAsientosResumenAttribute(): string
    {
        $info = $this->obtenerInfoMapa();
        $filas = $info['filas'];
        $pasillo = $info['pasillo'] ? 'con pasillo' : 'sin pasillo';
        $capacidad = $info['capacidad_total'];

        return "{$filas} filas, {$pasillo} ({$capacidad} asientos)";
    }
}

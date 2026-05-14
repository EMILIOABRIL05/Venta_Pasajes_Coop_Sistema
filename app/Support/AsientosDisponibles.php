<?php

namespace App\Support;

use App\Models\Boleto;
use App\Models\Viaje;
use Illuminate\Support\Collection;

final class AsientosDisponibles
{
    public static function capacidad(Viaje $viaje): int
    {
        $viaje->loadMissing('bus');

        return max(1, (int) ($viaje->bus->numero_asientos ?? 40));
    }

    public static function asientosOcupados(Viaje $viaje, bool $bloquear = false): Collection
    {
        $query = Boleto::query()
            ->where('frecuencia_id', $viaje->frecuencia_id);

        if ($bloquear) {
            $query->lockForUpdate();
        }

        return $query
            ->pluck('numero_asiento')
            ->map(static fn ($asiento) => trim((string) $asiento))
            ->filter()
            ->unique()
            ->values();
    }

    public static function asientosDisponibles(Viaje $viaje, bool $bloquear = false): array
    {
        $capacidad = self::capacidad($viaje);
        $ocupados = self::asientosOcupados($viaje, $bloquear);

        if ($ocupados->isEmpty()) {
            return array_map(static fn (int $asiento) => (string) $asiento, range(1, $capacidad));
        }

        $ocupadosLookup = $ocupados->flip();
        $disponibles = [];

        for ($asiento = 1; $asiento <= $capacidad; $asiento++) {
            $numeroAsiento = (string) $asiento;

            if (! $ocupadosLookup->has($numeroAsiento)) {
                $disponibles[] = $numeroAsiento;
            }
        }

        return $disponibles;
    }

    public static function primerDisponible(Viaje $viaje, bool $bloquear = false): ?string
    {
        return self::asientosDisponibles($viaje, $bloquear)[0] ?? null;
    }

    public static function resumen(Viaje $viaje, bool $bloquear = false): array
    {
        $capacidad = self::capacidad($viaje);
        $ocupados = self::asientosOcupados($viaje, $bloquear);
        $disponibles = self::asientosDisponibles($viaje, $bloquear);

        return [
            'capacidad_total' => $capacidad,
            'asientos_ocupados' => $ocupados->values()->all(),
            'asientos_disponibles' => $disponibles,
            'ocupacion' => $capacidad > 0 ? round(($ocupados->count() / $capacidad) * 100, 2) : 0.0,
        ];
    }
}
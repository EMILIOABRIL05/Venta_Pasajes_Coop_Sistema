<?php

namespace App\Support;

final class DescuentoPorEdad
{
    public static function porcentaje(): int
    {
        return (int) config('pasajes.descuento_por_edad', 50);
    }

    public static function edadNinoMaxima(): int
    {
        return (int) config('pasajes.edad_nino_maxima', 12);
    }

    public static function edadTerceraEdadMinima(): int
    {
        return (int) config('pasajes.edad_tercera_maxima', 65);
    }

    public static function tipo(?int $edad): ?string
    {
        if ($edad === null) {
            return null;
        }

        if ($edad >= self::edadTerceraEdadMinima()) {
            return 'tercera_edad';
        }

        if ($edad <= self::edadNinoMaxima()) {
            return 'nino';
        }

        return null;
    }

    public static function aplica(?int $edad): bool
    {
        return self::tipo($edad) !== null;
    }

    public static function monto(float $precioBase, ?int $edad): float
    {
        if (! self::aplica($edad)) {
            return 0.0;
        }

        return round($precioBase * self::porcentaje() / 100, 2);
    }

    public static function precioFinal(float $precioBase, ?int $edad): float
    {
        return round(max(0, $precioBase - self::monto($precioBase, $edad)), 2);
    }
}
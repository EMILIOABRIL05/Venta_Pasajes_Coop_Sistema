<?php

namespace App\Services;

use App\Support\DescuentoPorEdad;

class PricingService
{
    /**
     * Calcula el precio final de un boleto aplicando recargo de categoría
     * y descuento por edad si corresponde.
     *
     * Fórmula: precio_final = (precio_base + recargo) - descuento_por_edad
     *
     * @param  float   $precioBase  Precio base de la ruta/frecuencia
     * @param  float   $recargo     Recargo fijo de la categoría de asiento (default: 0)
     * @param  int|null $edad       Edad del pasajero para descuento (default: null)
     * @param  bool     $tieneDiscapacidad  Aplica descuento legal por discapacidad
     * @return float   Precio final redondeado a 2 decimales
     */
    public function calcularPrecioFinal(
        float $precioBase,
        float $recargo = 0.0,
        ?int $edad = null,
        bool $tieneDiscapacidad = false,
    ): float {
        $precioAntesDescuento = $precioBase + $recargo;

        if ($tieneDiscapacidad) {
            $descuento = round($precioAntesDescuento * DescuentoPorEdad::porcentaje() / 100, 2);

            return round(max(0, $precioAntesDescuento - $descuento), 2);
        }

        if ($edad !== null && DescuentoPorEdad::aplica($edad)) {
            return DescuentoPorEdad::precioFinal($precioAntesDescuento, $edad);
        }

        return round($precioAntesDescuento, 2);
    }

    /**
     * Calcula el total de una venta sumando los precios finales de cada boleto.
     *
     * @param  array<int, array{precio_base: float, recargo: float, edad: int|null, tiene_discapacidad?: bool}> $items
     * @return float
     */
    public function calcularTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $total += $this->calcularPrecioFinal(
                $item['precio_base'],
                $item['recargo'] ?? 0.0,
                $item['edad'] ?? null,
                $item['tiene_discapacidad'] ?? false,
            );
        }

        return round($total, 2);
    }
}

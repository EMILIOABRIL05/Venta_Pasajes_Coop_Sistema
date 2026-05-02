<?php

namespace App\Traits;

use App\Support\DescuentoPorEdad;

trait CalculaDescuentoPorEdad
{
    public function edadParaDescuento(): ?int
    {
        if (property_exists($this, 'edad') && is_numeric($this->edad)) {
            return (int) $this->edad;
        }

        if (property_exists($this, 'fecha_nacimiento') && $this->fecha_nacimiento) {
            return (int) $this->fecha_nacimiento->age;
        }

        return null;
    }

    public function tipoDescuentoPorEdad(): ?string
    {
        return DescuentoPorEdad::tipo($this->edadParaDescuento());
    }

    public function aplicaDescuentoPorEdad(): bool
    {
        return DescuentoPorEdad::aplica($this->edadParaDescuento());
    }

    public function porcentajeDescuentoPorEdad(): int
    {
        return $this->aplicaDescuentoPorEdad() ? DescuentoPorEdad::porcentaje() : 0;
    }

    public function montoDescuentoPorEdad(float $precioBase): float
    {
        return DescuentoPorEdad::monto($precioBase, $this->edadParaDescuento());
    }

    public function precioFinalConDescuentoPorEdad(float $precioBase): float
    {
        return DescuentoPorEdad::precioFinal($precioBase, $this->edadParaDescuento());
    }
}
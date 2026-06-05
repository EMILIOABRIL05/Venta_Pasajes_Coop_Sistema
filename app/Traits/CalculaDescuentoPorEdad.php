<?php

namespace App\Traits;

use App\Support\DescuentoPorEdad;

trait CalculaDescuentoPorEdad
{
    public function edadParaDescuento(): ?int
    {
        $edad = data_get($this, 'edad');

        if (is_numeric($edad)) {
            return (int) $edad;
        }

        $fechaNacimiento = data_get($this, 'fecha_nacimiento');

        if ($fechaNacimiento) {
            return (int) $fechaNacimiento->age;
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
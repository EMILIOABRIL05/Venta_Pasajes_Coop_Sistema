<?php

namespace Tests\Unit;

use App\Models\Pasajero;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DescuentoPorEdadTest extends TestCase
{
    public function test_aplica_descuento_a_tercera_edad_desde_fecha_de_nacimiento(): void
    {
        $usuario = new User([
            'fecha_nacimiento' => Carbon::parse('1950-01-01'),
        ]);

        $this->assertTrue($usuario->esTerceraEdad());
        $this->assertTrue($usuario->aplicaDescuentoPorEdad());
        $this->assertSame('tercera_edad', $usuario->tipoDescuentoPorEdad());
        $this->assertSame(50, $usuario->porcentajeDescuentoPorEdad());
        $this->assertSame(25.0, $usuario->montoDescuentoPorEdad(50.0));
        $this->assertSame(25.0, $usuario->precioFinalConDescuentoPorEdad(50.0));
    }

    public function test_aplica_descuento_a_nino_desde_edad_directa(): void
    {
        $pasajero = new Pasajero([
            'edad' => 8,
        ]);

        $this->assertTrue($pasajero->esNino());
        $this->assertTrue($pasajero->aplicaDescuentoPorEdad());
        $this->assertSame('nino', $pasajero->tipoDescuentoPorEdad());
        $this->assertSame(50, $pasajero->porcentajeDescuentoPorEdad());
        $this->assertSame(10.0, $pasajero->montoDescuentoPorEdad(20.0));
        $this->assertSame(10.0, $pasajero->precioFinalConDescuentoPorEdad(20.0));
    }

    public function test_no_aplica_descuento_a_adulto(): void
    {
        $pasajero = new Pasajero([
            'edad' => 32,
        ]);

        $this->assertFalse($pasajero->aplicaDescuentoPorEdad());
        $this->assertNull($pasajero->tipoDescuentoPorEdad());
        $this->assertSame(0, $pasajero->porcentajeDescuentoPorEdad());
        $this->assertSame(0.0, $pasajero->montoDescuentoPorEdad(20.0));
        $this->assertSame(20.0, $pasajero->precioFinalConDescuentoPorEdad(20.0));
    }
}
<?php

namespace Tests\Unit;

use App\Services\PricingService;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    public function test_calcula_precio_con_recargo_y_descuento_por_edad(): void
    {
        $service = new PricingService();

        $this->assertSame(7.5, $service->calcularPrecioFinal(10.0, 5.0, 70));
    }

    public function test_calcula_precio_con_recargo_y_descuento_por_discapacidad(): void
    {
        $service = new PricingService();

        $this->assertSame(7.5, $service->calcularPrecioFinal(10.0, 5.0, 32, true));
    }
}

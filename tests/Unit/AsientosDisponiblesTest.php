<?php

namespace Tests\Unit;

use App\Models\Boleto;
use App\Models\Bus;
use App\Models\Frecuencia;
use App\Models\Pasajero;
use App\Models\Parada;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Venta;
use App\Models\Viaje;
use App\Support\AsientosDisponibles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsientosDisponiblesTest extends TestCase
{
    use RefreshDatabase;

    private function crearViajeConBoletos(array $asientosOcupados = [], int $capacidad = 10): Viaje
    {
        $cajero = User::factory()->create();

        $bus = Bus::create([
            'placa' => 'TST-2001',
            'marca_chasis' => 'Hino',
            'carroceria' => 'Prueba',
            'anio' => 2020,
            'numero_asientos' => $capacidad,
            'estado' => 'disponible',
            'mapa_asientos' => Bus::generarEstructuraAsientos(3, true),
        ]);

        $origen = Parada::create([
            'nombre' => 'Ambato',
            'ciudad' => 'Ambato',
        ]);

        $destino = Parada::create([
            'nombre' => 'Quito',
            'ciudad' => 'Quito',
        ]);

        $ruta = Ruta::create([
            'origen_id' => $origen->id,
            'destino_id' => $destino->id,
            'precio_base' => 12.5,
            'tiempo_estimado_minutos' => 120,
        ]);

        $frecuencia = Frecuencia::create([
            'ruta_id' => $ruta->id,
            'hora_salida' => '08:30:00',
        ]);

        $viaje = Viaje::create([
            'fecha' => now()->toDateString(),
            'frecuencia_id' => $frecuencia->id,
            'bus_id' => $bus->id,
            'estado' => 'En Terminal',
        ]);

        foreach ($asientosOcupados as $asiento) {
            $pasajero = Pasajero::create([
                'cedula' => str_pad((string) $asiento, 10, '1', STR_PAD_LEFT),
                'nombre_completo' => 'Pasajero ' . $asiento,
                'edad' => 30,
            ]);

            $venta = Venta::create([
                'user_id' => $cajero->id,
                'total' => 12.5,
                'estado' => 'Pagada',
            ]);

            Boleto::create([
                'venta_id' => $venta->id,
                'pasajero_id' => $pasajero->id,
                'frecuencia_id' => $frecuencia->id,
                'numero_asiento' => (string) $asiento,
                'precio_final' => 12.5,
            ]);
        }

        return $viaje->load(['bus', 'frecuencia']);
    }

    public function test_calcula_asientos_disponibles_en_orden(): void
    {
        $viaje = $this->crearViajeConBoletos([1, 3], 10);

        $this->assertSame(['2', '4', '5', '6', '7', '8', '9', '10'], AsientosDisponibles::asientosDisponibles($viaje));
        $this->assertSame('2', AsientosDisponibles::primerDisponible($viaje));

        $resumen = AsientosDisponibles::resumen($viaje);

        $this->assertSame(10, $resumen['capacidad_total']);
        $this->assertSame(['1', '3'], $resumen['asientos_ocupados']);
        $this->assertSame(['2', '4', '5', '6', '7', '8', '9', '10'], $resumen['asientos_disponibles']);
        $this->assertSame(20.0, $resumen['ocupacion']);
    }

    public function test_devuelve_null_cuando_no_quedan_asientos_disponibles(): void
    {
        $viaje = $this->crearViajeConBoletos([1, 2], 2);

        $this->assertSame([], AsientosDisponibles::asientosDisponibles($viaje));
        $this->assertNull(AsientosDisponibles::primerDisponible($viaje));
    }
}
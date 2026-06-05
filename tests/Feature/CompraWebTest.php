<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Parada;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Viaje;
use App\Models\Frecuencia;
use App\Models\Pasajero;
use App\Models\Venta;
use App\Models\Boleto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompraWebTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    public function test_compra_web_calculates_total_with_base_and_recargo_and_discounts()
    {
        $user = $this->makeUser();

        $origen = Parada::create(['nombre' => 'Ambato', 'ciudad' => 'Ambato']);
        $destino = Parada::create(['nombre' => 'Quito', 'ciudad' => 'Quito']);
        
        $ruta = Ruta::create([
            'origen_id' => $origen->id,
            'destino_id' => $destino->id,
            'precio_base' => 10.00,
            'tiempo_estimado_minutos' => 120
        ]);

        $frecuencia = Frecuencia::create([
            'ruta_id' => $ruta->id,
            'hora_salida' => '08:00:00'
        ]);

        $bus = Bus::create([
            'placa' => 'AAA-1234',
            'marca_chasis' => 'Hino',
            'carroceria' => 'Prueba',
            'anio' => 2020,
            'numero_asientos' => 40,
            'estado' => 'disponible',
            'mapa_asientos' => Bus::generarEstructuraAsientos(10, true),
        ]);

        $viaje = Viaje::create([
            'fecha' => now()->toDateString(),
            'frecuencia_id' => $frecuencia->id,
            'bus_id' => $bus->id,
            'chofer_user_id' => $user->id,
            'estado' => 'Programado'
        ]);

        $test = Livewire::actingAs($user)
            ->test(\App\Livewire\Web\CompraWeb::class, ['viajeId' => $viaje->id])
            ->assertSet('total', 0)
            ->call('seleccionarAsiento', 5)
            ->assertSet('total', 10.00)
            ->set('tipoAsiento', 'vip')
            ->assertSet('total', 15.00)
            ->set('datosPasajeros.5.nombre', 'Carlos Lopez')
            ->set('datosPasajeros.5.cedula', '1712345678')
            ->set('datosPasajeros.5.edad', 70)
            ->assertSet('total', 7.50)
            ->call('confirmarVenta');

        $latestVenta = Venta::latest('id')->first();
        $this->assertNotNull($latestVenta);
        $test->assertRedirect(route('pago', ['ventaId' => $latestVenta->id]));

        $this->assertDatabaseHas('ventas', [
            'user_id' => $user->id,
            'total' => 7.50
        ]);

        $this->assertDatabaseHas('boletos', [
            'numero_asiento' => '5',
            'precio_final' => 7.50
        ]);
    }

    /**
     * Test adicional que simula intento de venta duplicada
     * comprobando que la migración del índice único bloquea la operación.
     */
    public function test_compra_web_captures_unique_index_exception_on_race_condition()
    {
        $user = $this->makeUser();

        $origen = Parada::create(['nombre' => 'Ambato', 'ciudad' => 'Ambato']);
        $destino = Parada::create(['nombre' => 'Quito', 'ciudad' => 'Quito']);
        
        $ruta = Ruta::create([
            'origen_id' => $origen->id,
            'destino_id' => $destino->id,
            'precio_base' => 10.00,
            'tiempo_estimado_minutos' => 120
        ]);

        $frecuencia = Frecuencia::create([
            'ruta_id' => $ruta->id,
            'hora_salida' => '08:00:00'
        ]);

        $bus = Bus::create([
            'placa' => 'AAA-1234',
            'marca_chasis' => 'Hino',
            'carroceria' => 'Prueba',
            'anio' => 2020,
            'numero_asientos' => 40,
            'estado' => 'disponible',
            'mapa_asientos' => Bus::generarEstructuraAsientos(10, true),
        ]);

        $viaje = Viaje::create([
            'fecha' => now()->toDateString(),
            'frecuencia_id' => $frecuencia->id,
            'bus_id' => $bus->id,
            'chofer_user_id' => $user->id,
            'estado' => 'Programado'
        ]);

        $pasajeroFantasma = Pasajero::create([
            'nombre_completo' => 'Usuario Concurrente',
            'cedula' => '1799999999',
            'edad' => 30
        ]);

        $ventaFantasma = Venta::create([
            'user_id' => $user->id,
            'cliente_id' => $user->id,
            'total' => 10.00,
            'estado' => 'Pendiente'
        ]);

        // 1. El fantasma compra el asiento 5
        Boleto::create([
            'venta_id'       => $ventaFantasma->id,
            'pasajero_id'    => $pasajeroFantasma->id,
            'viaje_id'       => $viaje->id, 
            'frecuencia_id'  => $frecuencia->id,
            'numero_asiento' => '5',
            'precio_final'   => 10.00
        ]);

        // 2. Le decimos al test que ESPERE un error de base de datos
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // 3. Intentamos forzar la compra del mismo asiento 5 para que el índice único lo bloquee
        Boleto::create([
            'venta_id'       => $ventaFantasma->id,
            'pasajero_id'    => $pasajeroFantasma->id,
            'viaje_id'       => $viaje->id, 
            'frecuencia_id'  => $frecuencia->id,
            'numero_asiento' => '5',
            'precio_final'   => 10.00
        ]);
    }
}

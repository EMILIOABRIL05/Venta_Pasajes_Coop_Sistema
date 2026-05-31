<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\CategoriaBus;
use App\Models\Parada;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Viaje;
use App\Models\Frecuencia;
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

        // 1. Crear dependencias
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

        $categoria = CategoriaBus::create([
            'nombre' => 'Normal',
            'descripcion' => 'Prueba'
        ]);

        $bus = Bus::create([
            'categoria_bus_id' => $categoria->id,
            'disco' => '100',
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

        // 2. Probar componente Livewire
        $test = Livewire::actingAs($user)
            ->test(\App\Livewire\Web\CompraWeb::class, ['viajeId' => $viaje->id])
            // Verificar estado inicial
            ->assertSet('total', 0)
            
            // Seleccionar asiento (precio estándar por defecto: recargo $0)
            ->call('seleccionarAsiento', 5)
            ->assertSet('total', 10.00)
            
            // Cambiar categoría a VIP (recargo $5.00, total debe ser $15.00)
            ->set('tipoAsiento', 'vip')
            ->assertSet('total', 15.00)
            
            // Introducir datos del pasajero
            ->set('datosPasajeros.5.nombre', 'Carlos Lopez')
            ->set('datosPasajeros.5.cedula', '1712345678')
            
            // Aplicar edad de tercera edad (70 años) -> descuento del 50% sobre total (base + recargo)
            // (10.00 base + 5.00 recargo = 15.00) -> 50% de descuento = 7.50 total
            ->set('datosPasajeros.5.edad', 70)
            ->assertSet('total', 7.50)
            
            // Confirmar la venta
            ->call('confirmarVenta');

        $latestVenta = \App\Models\Venta::latest('id')->first();
        $this->assertNotNull($latestVenta);
        $test->assertRedirect(route('pago', ['ventaId' => $latestVenta->id]));

        // 3. Verificar que se haya guardado correctamente en la BD
        $this->assertDatabaseHas('ventas', [
            'user_id' => $user->id,
            'total' => 7.50
        ]);

        $this->assertDatabaseHas('boletos', [
            'numero_asiento' => '5',
            'precio_final' => 7.50
        ]);
    }
}

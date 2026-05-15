<?php

namespace Tests\Feature;

use App\Http\Controllers\Ventanilla\VentaController;
use App\Models\Boleto;
use App\Models\Frecuencia;
use App\Models\Parada;
use App\Models\Pasajero;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnulacionBoletoTest extends TestCase
{
    use RefreshDatabase;

    private function setupBasics()
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'oficinista', 'guard_name' => 'web']);
        $user->assignRole('oficinista');

        $origen = Parada::create(['nombre' => 'Ambato', 'ciudad' => 'Ambato']);
        $destino = Parada::create(['nombre' => 'Quito', 'ciudad' => 'Quito']);
        $ruta = Ruta::create(['origen_id' => $origen->id, 'destino_id' => $destino->id, 'precio_base' => 10, 'tiempo_estimado_minutos' => 120]);
        $frecuencia = Frecuencia::create(['ruta_id' => $ruta->id, 'hora_salida' => '08:00:00']);

        $pasajero = Pasajero::create([
            'cedula' => '1234567890',
            'nombre_completo' => 'Juan Perez',
            'fecha_nacimiento' => '1990-01-01',
            'edad' => 36,
            'genero' => 'masculino',
            'correo' => 'juan@example.com',
            'telefono' => '0999999999'
        ]);

        return [$user, $frecuencia, $pasajero];
    }

    /** @test */
    public function venta_guarda_correctamente_los_boletos()
    {
        [$user, $frecuencia, $pasajero] = $this->setupBasics();

        $response = $this->actingAs($user)->post(route('ventanilla.ventas.store'), [
            'frecuencia_id' => $frecuencia->id,
            'pasajero_id' => $pasajero->id,
            'asientos' => [5, 6],
            'precio_unitario' => 10.00,
            // Agregamos bus_id para que pase la validación en el store
            'bus_id' => \App\Models\Bus::factory()->create()->id ?? \App\Models\Bus::create(['placa' => 'AAA-1111', 'numero_asientos' => 40, 'estado' => 'disponible', 'categoria_bus_id' => \App\Models\CategoriaBus::create(['nombre' => 'Normal', 'capacidad' => 40])->id])->id
        ]);

        // Asegurarse de que la venta y los boletos se hayan guardado en la base de datos
        $this->assertDatabaseHas('ventas', [
            'user_id' => $user->id,
            'total' => 20.00
        ]);

        $this->assertDatabaseHas('boletos', [
            'pasajero_id' => $pasajero->id,
            'numero_asiento' => '5',
            'precio_final' => 10.00
        ]);

        $this->assertDatabaseHas('boletos', [
            'pasajero_id' => $pasajero->id,
            'numero_asiento' => '6',
            'precio_final' => 10.00
        ]);
    }

    /** @test */
    public function sistema_impide_anular_boleto_despues_de_30_minutos()
    {
        [$user, $frecuencia, $pasajero] = $this->setupBasics();

        // Crear una venta y un boleto directamente en la DB simulando que pasó el tiempo
        $venta = Venta::create([
            'user_id' => $user->id,
            'total' => 10.00,
            'created_at' => Carbon::now()->subMinutes(35) // Tiene 35 minutos de antigüedad
        ]);

        $boleto = Boleto::create([
            'id' => (string) Str::uuid(),
            'venta_id' => $venta->id,
            'frecuencia_id' => $frecuencia->id,
            'pasajero_id' => $pasajero->id,
            'numero_asiento' => '12',
            'precio_final' => 10.00,
            'estado' => 'Vendido',
            'created_at' => Carbon::now()->subMinutes(35)
        ]);

        // Instanciar el controlador y llamar el método directamente
        $controller = new VentaController();
        $response = $controller->anularBoleto($boleto->id);

        // La anulación debería retornar un redirect con error
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
        $this->assertEquals('Tiempo límite de anulación excedido.', session('error'));

        // El boleto no debe haber sido eliminado lógicamente (sigue en la BD sin deleted_at)
        $this->assertDatabaseHas('boletos', [
            'id' => $boleto->id,
            'estado' => 'Vendido',
            'deleted_at' => null
        ]);
    }
}

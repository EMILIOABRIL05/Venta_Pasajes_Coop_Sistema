<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\CategoriaBus;
use App\Models\Parada;
use App\Models\Pasajero;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VentasVentanillaTest extends TestCase
{
    use RefreshDatabase;

    private function makeOficinista(): User
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'oficinista', 'guard_name' => 'web']);
        $user->assignRole('oficinista');
        return $user;
    }

    public function test_ventanilla_can_process_atomic_transaction()
    {
        $user = $this->makeOficinista();

        $origen = Parada::create(['nombre' => 'Ambato', 'ciudad' => 'Ambato']);
        $destino = Parada::create(['nombre' => 'Quito', 'ciudad' => 'Quito']);
        $ruta = Ruta::create(['origen_id' => $origen->id, 'destino_id' => $destino->id, 'precio_base' => 10, 'tiempo_estimado_minutos' => 120]);
        $frecuencia = \App\Models\Frecuencia::create(['ruta_id' => $ruta->id, 'hora_salida' => '08:00:00']);
        
        $pasajero = Pasajero::create([
            'cedula' => '1234567890',
            'nombre_completo' => 'Juan Perez',
            'fecha_nacimiento' => '1990-01-01',
            'edad' => 36,
            'genero' => 'masculino',
            'correo' => 'juan@example.com',
            'telefono' => '0999999999'
        ]);

        $response = $this->actingAs($user)->post(route('ventanilla.ventas.store'), [
            'ruta_id' => $ruta->id,
            'cedula' => $pasajero->cedula,
            'nombre_completo' => $pasajero->nombre_completo,
            'edad' => $pasajero->edad,
            'asientos' => [5, 6],
            'precio_unitario' => 10.00
        ]);

        if (session()->has('error')) {
            dd(session('error'));
        }
        $response->assertSessionMissing('error');
        $response->assertSessionHas('venta_exitosa');

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
}

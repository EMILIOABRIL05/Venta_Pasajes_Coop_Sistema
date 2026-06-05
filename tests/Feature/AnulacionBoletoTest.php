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
            'ruta_id' => $frecuencia->ruta_id,
            'cedula' => $pasajero->cedula,
            'nombre_completo' => $pasajero->nombre_completo,
            'edad' => $pasajero->edad,
            'asientos' => [5, 6],
            'precio_unitario' => 10.00,
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

        // Crear una venta y un boleto directamente en la DB
        $venta = Venta::create([
            'user_id' => $user->id,
            'total' => 10.00,
        ]);

        \Illuminate\Support\Facades\DB::table('ventas')
            ->where('id', $venta->id)
            ->update(['created_at' => Carbon::now()->subMinutes(35)]);

        $boleto = Boleto::create([
            'id' => (string) Str::uuid(),
            'venta_id' => $venta->id,
            'frecuencia_id' => $frecuencia->id,
            'pasajero_id' => $pasajero->id,
            'numero_asiento' => '12',
            'precio_final' => 10.00,
            'estado' => 'Vendido',
        ]);

        \Illuminate\Support\Facades\DB::table('boletos')
            ->where('id', $boleto->id)
            ->update(['created_at' => Carbon::now()->subMinutes(35)]);

        $boleto->refresh();

        $response = $this->actingAs($user)->post(route('ventanilla.ventas.boleto.anular', $boleto->id));

        // La anulación debería retornar un redirect con error
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Tiempo límite de anulación excedido.');

        // El boleto no debe haber sido eliminado lógicamente (sigue en la BD sin deleted_at)
        $this->assertDatabaseHas('boletos', [
            'id' => $boleto->id,
            'estado' => 'Vendido',
            'deleted_at' => null
        ]);
    }
}

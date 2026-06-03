<?php

namespace Tests\Feature;

use App\Livewire\Chofer\PanelPrincipal;
use App\Models\Boleto;
use App\Models\BoletoValidacion;
use App\Models\Bus;
use App\Models\Frecuencia;
use App\Models\Parada;
use App\Models\Pasajero;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Venta;
use App\Models\Viaje;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelPrincipalChoferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function crearInfraestructuraViaje(User $chofer): array
    {
        Carbon::setTestNow(Carbon::parse('2026-05-13 09:00:00'));

        $bus = Bus::create([
            'placa' => 'TST-1001',
            'marca_chasis' => 'Hino',
            'carroceria' => 'Imce',
            'anio' => 2020,
            'numero_asientos' => 10,
            'estado' => 'disponible',
            'mapa_asientos' => Bus::generarEstructuraAsientos(3, true),
        ]);

        $origen = Parada::create(['nombre' => 'Ambato', 'ciudad' => 'Ambato']);
        $destino = Parada::create(['nombre' => 'Quito', 'ciudad' => 'Quito']);
        $ruta = Ruta::create([
            'origen_id' => $origen->id,
            'destino_id' => $destino->id,
            'precio_base' => 12.5,
            'tiempo_estimado_minutos' => 120,
        ]);
        $frecuencia = Frecuencia::create(['ruta_id' => $ruta->id, 'hora_salida' => '08:30:00']);

        $viaje = Viaje::create([
            'fecha' => now()->toDateString(),
            'frecuencia_id' => $frecuencia->id,
            'bus_id' => $bus->id,
            'chofer_user_id' => $chofer->id,
            'estado' => 'En Terminal',
        ]);

        return compact('bus', 'ruta', 'frecuencia', 'viaje');
    }

    public function test_validar_abordaje_registra_a_bordo_cuando_el_boleto_coincide_con_la_frecuencia(): void
    {
        $chofer = User::factory()->create(['tipo_usuario' => 'chofer']);
        $chofer->assignRole('chofer');

        ['frecuencia' => $frecuencia, 'viaje' => $viaje] = $this->crearInfraestructuraViaje($chofer);

        $pasajero = Pasajero::create([
            'cedula' => '1717171717',
            'nombre_completo' => 'Pasajero Test',
            'edad' => 28,
        ]);

        $venta = Venta::create([
            'user_id' => $chofer->id,
            'total' => 12.5,
            'estado' => 'Pagada',
        ]);

        $boleto = Boleto::create([
            'venta_id' => $venta->id,
            'pasajero_id' => $pasajero->id,
            'frecuencia_id' => $frecuencia->id,
            'numero_asiento' => '3',
            'precio_final' => 12.5,
        ]);

        Livewire::actingAs($chofer)
            ->test(PanelPrincipal::class)
            ->call('validarAbordaje', $boleto->id)
            ->assertSet('tipoMensajeAbordaje', 'success');

        $this->assertDatabaseHas('boleto_validaciones', [
            'boleto_id' => $boleto->id,
            'usuario_id' => $chofer->id,
            'estado' => 'validado',
            'observaciones' => 'A bordo',
        ]);

        Carbon::setTestNow();
    }

    public function test_validar_abordaje_rechaza_boleto_de_otra_frecuencia(): void
    {
        $chofer = User::factory()->create(['tipo_usuario' => 'chofer']);
        $chofer->assignRole('chofer');

        ['frecuencia' => $frecuenciaCorrecta] = $this->crearInfraestructuraViaje($chofer);

        $origen = Parada::create(['nombre' => 'Puyo', 'ciudad' => 'Puyo']);
        $destino = Parada::create(['nombre' => 'Tena', 'ciudad' => 'Tena']);
        $otraRuta = Ruta::create([
            'origen_id' => $origen->id,
            'destino_id' => $destino->id,
            'precio_base' => 20,
            'tiempo_estimado_minutos' => 90,
        ]);
        $otraFrecuencia = Frecuencia::create(['ruta_id' => $otraRuta->id, 'hora_salida' => '10:00:00']);

        $pasajero = Pasajero::create([
            'cedula' => '1818181818',
            'nombre_completo' => 'Otro Pasajero',
            'edad' => 30,
        ]);

        $venta = Venta::create([
            'user_id' => $chofer->id,
            'total' => 20,
            'estado' => 'Pagada',
        ]);

        $boletoOtro = Boleto::create([
            'venta_id' => $venta->id,
            'pasajero_id' => $pasajero->id,
            'frecuencia_id' => $otraFrecuencia->id,
            'numero_asiento' => '1',
            'precio_final' => 20,
        ]);

        Livewire::actingAs($chofer)
            ->test(PanelPrincipal::class)
            ->call('validarAbordaje', $boletoOtro->id)
            ->assertSet('tipoMensajeAbordaje', 'error');

        $this->assertDatabaseMissing('boleto_validaciones', [
            'boleto_id' => $boletoOtro->id,
            'observaciones' => 'A bordo',
        ]);

        Carbon::setTestNow();
    }

    public function test_venta_express_crea_venta_boleto_y_pago(): void
    {
        $chofer = User::factory()->create(['tipo_usuario' => 'chofer']);
        $chofer->assignRole('chofer');

        ['frecuencia' => $frecuencia] = $this->crearInfraestructuraViaje($chofer);

        Livewire::actingAs($chofer)
            ->test(PanelPrincipal::class)
            ->call('ventaExpress')
            ->assertSet('tipoMensajeExpress', 'success');

        $this->assertDatabaseHas('ventas', [
            'user_id' => $chofer->id,
            'estado' => 'Pagada',
        ]);

        $this->assertDatabaseHas('boletos', [
            'frecuencia_id' => $frecuencia->id,
            'numero_asiento' => '1',
        ]);

        $this->assertDatabaseHas('pagos', [
            'metodo_pago' => 'venta_express',
        ]);

        Carbon::setTestNow();
    }

    public function test_validar_abordaje_idempotente_si_ya_esta_a_bordo(): void
    {
        $chofer = User::factory()->create(['tipo_usuario' => 'chofer']);
        $chofer->assignRole('chofer');

        ['frecuencia' => $frecuencia] = $this->crearInfraestructuraViaje($chofer);

        $pasajero = Pasajero::create([
            'cedula' => '1919191919',
            'nombre_completo' => 'Pasajero Abordo',
            'edad' => 40,
        ]);

        $venta = Venta::create([
            'user_id' => $chofer->id,
            'total' => 12.5,
            'estado' => 'Pagada',
        ]);

        $boleto = Boleto::create([
            'venta_id' => $venta->id,
            'pasajero_id' => $pasajero->id,
            'frecuencia_id' => $frecuencia->id,
            'numero_asiento' => '2',
            'precio_final' => 12.5,
        ]);

        BoletoValidacion::create([
            'boleto_id' => $boleto->id,
            'usuario_id' => $chofer->id,
            'fecha_validacion' => now(),
            'estado' => 'validado',
            'observaciones' => 'A bordo',
        ]);

        Livewire::actingAs($chofer)
            ->test(PanelPrincipal::class)
            ->call('validarAbordaje', $boleto->id)
            ->assertSet('tipoMensajeAbordaje', 'error');

        $this->assertEquals(1, BoletoValidacion::where('boleto_id', $boleto->id)->count());

        Carbon::setTestNow();
    }
}

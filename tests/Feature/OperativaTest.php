<?php

namespace Tests\Feature;

use App\Livewire\Operativa\HojaRuta;
use App\Models\Bus;
use App\Models\Frecuencia;
use App\Models\Parada;
use App\Models\Ruta;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperativaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeAdminUser(): User
    {
        $user = User::factory()->create();

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $user->assignRole('admin');

        return $user;
    }

    public function test_bus_cannot_be_assigned_to_different_routes_at_the_same_time(): void
    {
        $user = $this->makeAdminUser();

        $bus = Bus::create([
            'placa' => 'AAA-1234',
            'marca_chasis' => 'Hino',
            'carroceria' => 'Imce',
            'anio' => 2020,
            'numero_asientos' => 40,
            'estado' => 'disponible',
            'mapa_asientos' => Bus::generarEstructuraAsientos(10, true),
        ]);

        $origen1 = Parada::create(['nombre' => 'Ambato', 'ciudad' => 'Ambato']);
        $destino1 = Parada::create(['nombre' => 'Quito', 'ciudad' => 'Quito']);
        $destino2 = Parada::create(['nombre' => 'Guayaquil', 'ciudad' => 'Guayaquil']);

        $ruta1 = Ruta::create(['origen_id' => $origen1->id, 'destino_id' => $destino1->id, 'precio_base' => 10, 'tiempo_estimado_minutos' => 120]);
        $ruta2 = Ruta::create(['origen_id' => $origen1->id, 'destino_id' => $destino2->id, 'precio_base' => 12, 'tiempo_estimado_minutos' => 120]);

        $frecuencia1 = Frecuencia::create(['ruta_id' => $ruta1->id, 'hora_salida' => '08:00:00']);
        $frecuencia2 = Frecuencia::create(['ruta_id' => $ruta2->id, 'hora_salida' => '08:00:00']);

        // 1. Asignamos el bus a la primera frecuencia
        Livewire::actingAs($user)
            ->test(HojaRuta::class)
            ->set('fecha', '2026-05-10')
            ->set('frecuencia_id', $frecuencia1->id)
            ->set('bus_id', $bus->id)
            ->call('saveViaje');

        $this->assertDatabaseHas('viajes', [
            'frecuencia_id' => $frecuencia1->id,
            'bus_id' => $bus->id,
            'fecha' => '2026-05-10',
        ]);

        // 2. Intentamos asignar el MISMO bus, el MISMO día, a la MISMA hora, pero a OTRA frecuencia
        Livewire::actingAs($user)
            ->test(HojaRuta::class)
            ->set('fecha', '2026-05-10')
            ->set('frecuencia_id', $frecuencia2->id)
            ->set('bus_id', $bus->id)
            ->call('saveViaje');

        $this->assertDatabaseMissing('viajes', [
            'frecuencia_id' => $frecuencia2->id,
            'bus_id' => $bus->id,
            'fecha' => '2026-05-10',
        ]);
    }
}

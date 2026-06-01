<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\CategoriaAsiento;
use App\Models\Frecuencia;
use App\Models\Parada;
use App\Models\Ruta;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatosEntregaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_cargan_datos_reales_y_buses_con_asientos_categorizados(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(8, Parada::query()->count());
        $this->assertGreaterThanOrEqual(14, Ruta::query()->count());
        $this->assertGreaterThanOrEqual(3, CategoriaAsiento::query()->count());
        $this->assertGreaterThanOrEqual(3, Bus::query()->count());

        $rutaAmbatoQuito = Ruta::query()
            ->whereHas('origen', fn ($query) => $query->where('ciudad', 'Ambato'))
            ->whereHas('destino', fn ($query) => $query->where('ciudad', 'Quito'))
            ->first();

        $this->assertNotNull($rutaAmbatoQuito);
        $this->assertEquals('6.00', (string) $rutaAmbatoQuito->precio_base);
        $this->assertGreaterThanOrEqual(
            4,
            Frecuencia::query()->where('ruta_id', $rutaAmbatoQuito->id)->count()
        );

        $bus = Bus::query()->where('placa', 'TAA-1001')->firstOrFail();
        $this->assertArrayHasKey('categorias_asiento', $bus->mapa_asientos);
        $this->assertArrayHasKey('asientos', $bus->mapa_asientos);
        $this->assertCount($bus->numero_asientos, $bus->mapa_asientos['asientos']);
        $this->assertTrue(
            collect($bus->mapa_asientos['asientos'])->contains(fn ($asiento) => ! empty($asiento['categoria_id']))
        );
    }

    public function test_admin_puede_ver_panel_de_datos_de_entrega(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@cooperativa.test')->firstOrFail();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.datos-entrega'))
            ->assertOk()
            ->assertSee('Datos reales sembrados')
            ->assertSee('Ambato');
    }
}

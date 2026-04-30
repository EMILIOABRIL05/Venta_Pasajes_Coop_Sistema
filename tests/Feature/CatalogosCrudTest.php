<?php

namespace Tests\Feature;

use App\Models\CategoriaBus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogosCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_bus_categories_crud(): void
    {
        $user = $this->makeAdminUser();

        $response = $this
            ->actingAs($user)
            ->get(route('catalogos.categorias-bus'));

        $response->assertOk();
    }

    public function test_admin_can_open_buses_crud(): void
    {
        $user = $this->makeAdminUser();

        CategoriaBus::create([
            'nombre' => 'Normal',
            'descripcion' => 'Categoría base para pruebas.',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('catalogos.buses'));

        $response->assertOk();
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
}
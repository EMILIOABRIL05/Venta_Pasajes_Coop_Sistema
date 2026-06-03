<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_chofer_solo_ve_panel_operativo_del_chofer(): void
    {
        $chofer = User::factory()->create(['tipo_usuario' => 'chofer']);
        $chofer->assignRole('chofer');

        $this->actingAs($chofer);

        $html = view('components.sidebar-navigation')->render();

        $this->assertStringContainsString('Panel Chofer', $html);
        $this->assertStringNotContainsString('/solicitudes-cambio', $html);
        $this->assertStringNotContainsString('Panel Admin', $html);
        $this->assertStringNotContainsString('Gestion Reembolsos', $html);
    }

    public function test_cliente_ve_opciones_web_y_no_modulos_internos(): void
    {
        $cliente = User::factory()->create(['tipo_usuario' => 'oficinista']);
        $cliente->assignRole('cliente');

        $this->actingAs($cliente);

        $html = view('components.sidebar-navigation')->render();

        $this->assertStringContainsString('Mis Viajes', $html);
        $this->assertStringContainsString('Comprar Pasajes', $html);
        $this->assertStringContainsString('Reembolsos', $html);
        $this->assertStringNotContainsString('/solicitudes-cambio', $html);
        $this->assertStringNotContainsString('Panel Admin', $html);
        $this->assertStringNotContainsString('Cierre de Turno', $html);
    }

    public function test_admin_conserva_accesos_de_gestion(): void
    {
        $admin = User::factory()->create(['tipo_usuario' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $html = view('components.sidebar-navigation')->render();

        $this->assertStringContainsString('Panel Admin', $html);
        $this->assertStringContainsString('Reportes', $html);
        $this->assertStringContainsString('Gestion Reembolsos', $html);
        $this->assertStringContainsString('Panel Chofer', $html);
    }
}

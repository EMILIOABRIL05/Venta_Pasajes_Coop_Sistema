<?php

namespace Tests\Feature;

use App\Livewire\Catalogos\CuentasCrud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CuentasCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_cuentas_crud(): void
    {
        $admin = $this->makeAdminUser();

        $response = $this
            ->actingAs($admin)
            ->get(route('catalogos.cuentas'));

        $response->assertOk();
    }

    public function test_admin_can_create_oficinista_account(): void
    {
        $admin = $this->makeAdminUser();
        Mail::fake();

        Livewire::actingAs($admin)
            ->test(CuentasCrud::class)
            ->set('name', 'Oficinista Nuevo')
            ->set('email', 'oficinista.nuevo@cooperativa.test')
            ->set('cedula', '1900000101')
            ->set('telefono', '0987654501')
            ->set('fecha_nacimiento', '1991-04-15')
            ->set('tipo_usuario', 'oficinista')
            ->set('password', 'Oficina12345!')
            ->set('password_confirmation', 'Oficina12345!')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'oficinista.nuevo@cooperativa.test')->firstOrFail();

        $this->assertSame('oficinista', $user->tipo_usuario);
        $this->assertTrue($user->hasRole('oficinista'));
        $this->assertTrue(password_verify('Oficina12345!', $user->password));
    }

    public function test_admin_can_create_chofer_account(): void
    {
        $admin = $this->makeAdminUser();
        Mail::fake();

        Livewire::actingAs($admin)
            ->test(CuentasCrud::class)
            ->set('name', 'Chofer Nuevo')
            ->set('email', 'chofer.nuevo@cooperativa.test')
            ->set('cedula', '1900000102')
            ->set('telefono', '0987654502')
            ->set('fecha_nacimiento', '1988-09-22')
            ->set('tipo_usuario', 'chofer')
            ->set('password', 'Chofer12345!')
            ->set('password_confirmation', 'Chofer12345!')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'chofer.nuevo@cooperativa.test')->firstOrFail();

        $this->assertSame('chofer', $user->tipo_usuario);
        $this->assertTrue($user->hasRole('chofer'));
        $this->assertTrue(password_verify('Chofer12345!', $user->password));
    }

    private function makeAdminUser(): User
    {
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'name' => 'Admin Prueba',
            'email' => 'admin-cuentas@cooperativa.test',
            'cedula' => '1900000999',
            'telefono' => '0987654999',
            'fecha_nacimiento' => '1980-01-15',
            'tipo_usuario' => 'admin',
        ]);

        $user->assignRole($adminRole);

        return $user;
    }
}

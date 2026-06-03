<?php

namespace Tests\Feature;

use App\Livewire\Web\PagoWeb;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PagoWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_paypal_confirma_venta_y_registra_pago(): void
    {
        $user = User::factory()->create();
        $venta = $this->crearVentaPendiente($user, 22.50);

        Livewire::actingAs($user)
            ->test(PagoWeb::class, ['ventaId' => $venta->id])
            ->set('metodo_pago', 'paypal')
            ->set('referencia', 'PAYPAL-9AB12345')
            ->call('guardarPago')
            ->assertRedirect(route('mis-viajes'));

        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 'Pagada',
        ]);

        $this->assertDatabaseHas('pagos', [
            'venta_id' => $venta->id,
            'monto' => 22.50,
            'metodo_pago' => 'paypal',
            'referencia' => 'PAYPAL-9AB12345',
            'estado' => 'confirmado',
            'codigo_autorizacion' => 'PAYPAL-9AB12345',
        ]);
    }

    public function test_tarjeta_confirma_pago_sin_guardar_numero_completo_ni_cvv(): void
    {
        $user = User::factory()->create();
        $venta = $this->crearVentaPendiente($user, 18.00);

        Livewire::actingAs($user)
            ->test(PagoWeb::class, ['ventaId' => $venta->id])
            ->set('metodo_pago', 'tarjeta')
            ->set('titular_tarjeta', 'Cliente Prueba')
            ->set('numero_tarjeta', '4111111111111111')
            ->set('expiracion_tarjeta', now()->addYear()->format('m/y'))
            ->set('codigo_seguridad', '123')
            ->call('guardarPago')
            ->assertRedirect(route('mis-viajes'));

        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 'Pagada',
        ]);

        $pago = $venta->pagos()->firstOrFail();

        $this->assertSame('tarjeta', $pago->metodo_pago);
        $this->assertSame('confirmado', $pago->estado);
        $this->assertStringContainsString('TARJETA-1111-', $pago->referencia);
        $this->assertStringNotContainsString('4111111111111111', $pago->referencia);
        $this->assertStringNotContainsString('123', $pago->observaciones);
    }

    public function test_deuna_registra_codigo_y_deja_venta_en_validacion(): void
    {
        $user = User::factory()->create();
        $venta = $this->crearVentaPendiente($user, 15.75);

        Livewire::actingAs($user)
            ->test(PagoWeb::class, ['ventaId' => $venta->id])
            ->set('metodo_pago', 'deuna')
            ->set('codigo_deuna', 'DEUNA-QR-123456')
            ->call('guardarPago')
            ->assertRedirect(route('mis-viajes'));

        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 'Pendiente de Validación',
        ]);

        $this->assertDatabaseHas('pagos', [
            'venta_id' => $venta->id,
            'monto' => 15.75,
            'metodo_pago' => 'deuna',
            'referencia' => 'DEUNA-QR-123456',
            'estado' => 'pendiente_validacion',
        ]);
    }

    private function crearVentaPendiente(User $user, float $total): Venta
    {
        return Venta::create([
            'user_id' => $user->id,
            'cliente_id' => $user->id,
            'total' => $total,
            'estado' => 'Pendiente',
            'comprobante' => null,
        ]);
    }
}

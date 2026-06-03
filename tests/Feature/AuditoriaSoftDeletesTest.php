<?php

namespace Tests\Feature;

use App\Models\BoletoValidacion;
use App\Models\CierreTurno;
use App\Models\Pago;
use App\Models\Reembolso;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditoriaSoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tablas_de_auditoria_tienen_deleted_at(): void
    {
        foreach (['reembolsos', 'pagos', 'cierres_turno', 'boleto_validaciones'] as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'deleted_at'),
                "La tabla {$table} debe tener deleted_at."
            );
        }
    }

    public function test_modelos_de_auditoria_usan_soft_deletes(): void
    {
        foreach ([Reembolso::class, Pago::class, CierreTurno::class, BoletoValidacion::class] as $model) {
            $this->assertContains(SoftDeletes::class, class_uses_recursive($model));
        }
    }
}

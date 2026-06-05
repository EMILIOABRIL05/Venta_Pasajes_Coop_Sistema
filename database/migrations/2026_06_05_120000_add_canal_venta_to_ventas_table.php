<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Canal por el cual se realizó la venta: web o ventanilla
            $table->string('canal_venta', 20)->default('ventanilla')->after('comprobante');

            // Fecha en la que se confirmó el pago (relevante para ventas web)
            $table->timestamp('fecha_pago')->nullable()->after('canal_venta');
        });

        // user_id originalmente era non-nullable (cajero).
        // Para ventas web no existe cajero, por lo que debe permitir NULL.
        // Proceso manual: drop FK -> alter column -> re-add FK
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE ventas ALTER COLUMN user_id DROP NOT NULL');

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Revertir a non-nullable (solo funciona si no existen ventas con user_id NULL)
        DB::statement('ALTER TABLE ventas ALTER COLUMN user_id SET NOT NULL');

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->dropColumn(['canal_venta', 'fecha_pago']);
        });
    }
};

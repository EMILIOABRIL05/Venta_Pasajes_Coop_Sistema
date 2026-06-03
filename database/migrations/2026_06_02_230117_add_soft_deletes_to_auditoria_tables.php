<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reembolsos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('cierres_turno', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('boleto_validaciones', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('boleto_validaciones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('cierres_turno', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('reembolsos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            // Índice único para evitar ventas duplicadas sobre el mismo asiento en un viaje específico
            $table->unique(['viaje_id', 'numero_asiento'], 'idx_viaje_asiento_unico');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->dropUnique('idx_viaje_asiento_unico');
        });
    }
};
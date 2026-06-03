<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega viaje_id a boletos para que la disponibilidad de asientos
     * se vincule al viaje concreto (fecha + bus) y no solo a la frecuencia.
     *
     * Se define nullable para compatibilidad con boletos históricos que
     * no tienen viaje asociado; los boletos nuevos siempre lo llevarán.
     */
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->foreignId('viaje_id')
                ->nullable()
                ->after('frecuencia_id')
                ->constrained('viajes')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('viaje_id');
        });
    }
};

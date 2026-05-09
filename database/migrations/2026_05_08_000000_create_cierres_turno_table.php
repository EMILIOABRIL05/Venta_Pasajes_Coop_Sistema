<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para almacenar el registro de cierre de turno de cada cajero.
     *
     * Un cajero solo puede generar un cierre por fecha (unique: user_id + fecha).
     * Los montos se guardan en el momento del cierre para auditoría histórica.
     */
    public function up(): void
    {
        Schema::create('cierres_turno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Cajero
            $table->date('fecha');                            // Fecha del turno cerrado
            $table->decimal('total_bruto', 10, 2);           // SUM(ventas.total) del día
            $table->decimal('total_reembolsos', 10, 2)->default(0); // SUM(reembolsos aprobados)
            $table->decimal('total_neto', 10, 2);            // total_bruto - total_reembolsos
            $table->unsignedInteger('total_boletos');         // Conteo de boletos vendidos
            $table->timestamps();

            // Un cajero solo puede cerrar una vez por fecha
            $table->unique(['user_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_turno');
    }
};

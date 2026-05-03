<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el código de reserva alfanumérico legible a la tabla de boletos.
     *
     * Formato: AMB-{AÑO}-{4 chars aleatorios}  →  ej: AMB-2026-X4K9
     *
     * · nullable()  → permite migrar sin romper registros existentes.
     * · unique()    → garantía de unicidad a nivel de base de datos.
     * · after('id') → queda como segunda columna para visibilidad rápida.
     */
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->string('codigo_reserva', 20)
                  ->nullable()
                  ->unique()
                  ->after('id')
                  ->comment('Código legible para ventanilla. Ej: AMB-2026-X4K9');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->dropUnique(['codigo_reserva']);
            $table->dropColumn('codigo_reserva');
        });
    }
};

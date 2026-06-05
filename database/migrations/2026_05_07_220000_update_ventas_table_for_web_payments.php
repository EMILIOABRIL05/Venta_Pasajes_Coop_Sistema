<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Añadimos cliente_id para identificar al comprador web (separado del user_id que es el cajero)
            $table->foreignId('cliente_id')->nullable()->after('user_id')->constrained('users')->onDelete('restrict');
            // Estado de la venta (Pendiente, Pendiente de Validación, Pagada, etc.)
            $table->string('estado')->default('Pendiente')->after('total');
            // Ruta del archivo del comprobante
            $table->string('comprobante')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn(['cliente_id', 'estado', 'comprobante']);
        });
    }
};

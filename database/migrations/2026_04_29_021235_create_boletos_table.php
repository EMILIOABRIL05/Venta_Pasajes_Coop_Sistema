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
        Schema::create('boletos', function (Blueprint $table) {
            $table->uuid('id')->primary();                                              // UUID como llave primaria
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade'); // Si se elimina la venta, se elimina el boleto
            $table->foreignId('pasajero_id')->constrained('pasajeros')->onDelete('restrict');
            $table->string('numero_asiento');                                           // Ej: "12A", "5B"
            $table->decimal('precio_final', 8, 2);
            $table->timestamps();
            $table->softDeletes();                                                      // Borrado lógico (deleted_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};

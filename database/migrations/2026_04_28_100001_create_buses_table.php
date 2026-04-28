<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_bus_id')
                  ->constrained('categorias_bus')
                  ->onDelete('restrict');
            $table->string('placa')->unique();          // Ej: ABC-1234
            $table->string('marca_chasis');             // Ej: Mercedes Benz
            $table->string('carroceria');               // Ej: Marcopolo
            $table->integer('anio');
            $table->string('foto')->nullable();         // Path de la imagen
            $table->integer('numero_asientos');
            $table->json('mapa_asientos')->nullable();  // {"filas":10,"pasillo":true}
            $table->enum('estado', ['disponible', 'en_ruta', 'mantenimiento'])
                  ->default('disponible');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
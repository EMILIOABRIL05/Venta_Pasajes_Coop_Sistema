<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_cambio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo_solicitud');
            $table->text('descripcion');
            $table->enum('prioridad', ['Baja', 'Media', 'Alta']);
            $table->enum('estado_pipeline', [
                'Propuesto',
                'En Desarrollo',
                'Validado en Sandbox',
                'Mergado',
                'Desplegado',
                'Rechazado',
            ])->default('Propuesto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_cambio');
    }
};

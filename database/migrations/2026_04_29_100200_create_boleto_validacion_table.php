<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boleto_validaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boleto_id')->constrained('boletos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('fecha_validacion');
            $table->enum('estado', ['validado', 'rechazado'])->default('validado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleto_validaciones');
    }
};

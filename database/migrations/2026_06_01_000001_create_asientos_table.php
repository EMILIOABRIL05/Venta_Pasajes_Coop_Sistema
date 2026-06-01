<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->cascadeOnDelete();
            $table->unsignedInteger('numero');
            $table->enum('categoria', ['estandar', 'vip'])->default('estandar');
            $table->timestamps();

            $table->unique(['bus_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos');
    }
};
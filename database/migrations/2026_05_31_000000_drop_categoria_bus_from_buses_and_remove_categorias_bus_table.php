<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('buses') && Schema::hasColumn('buses', 'categoria_bus_id')) {
            Schema::table('buses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('categoria_bus_id');
            });
        }

        Schema::dropIfExists('categorias_bus');
    }

    public function down(): void
    {
        if (! Schema::hasTable('categorias_bus')) {
            Schema::create('categorias_bus', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('buses') && ! Schema::hasColumn('buses', 'categoria_bus_id')) {
            Schema::table('buses', function (Blueprint $table) {
                $table->foreignId('categoria_bus_id')
                    ->nullable()
                    ->constrained('categorias_bus')
                    ->nullOnDelete()
                    ->after('id');
            });
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cedula', 10)->unique()->nullable()->after('name');
            $table->string('telefono', 10)->nullable()->after('cedula');
            $table->date('fecha_nacimiento')->nullable()->after('telefono');
            $table->enum('tipo_usuario', ['admin', 'oficinista', 'chofer'])
                  ->default('oficinista')->after('fecha_nacimiento');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'telefono', 'fecha_nacimiento', 'tipo_usuario']);
            $table->dropSoftDeletes();
        });
    }
};
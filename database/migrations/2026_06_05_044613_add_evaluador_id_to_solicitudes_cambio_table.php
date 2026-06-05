<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_cambio', function (Blueprint $table) {
            $table->foreignId('evaluador_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_cambio', function (Blueprint $table) {
            $table->dropForeign(['evaluador_id']);
            $table->dropColumn('evaluador_id');
        });
    }
};

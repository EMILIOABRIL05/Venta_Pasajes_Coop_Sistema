<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boleto_validaciones', function (Blueprint $table) {
            $table->unique('boleto_id', 'boleto_validaciones_boleto_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('boleto_validaciones', function (Blueprint $table) {
            $table->dropUnique('boleto_validaciones_boleto_id_unique');
        });
    }
};

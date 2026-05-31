<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boletos', function (Blueprint $table) {
            // Hacemos que la columna vieja ya no sea obligatoria
            $table->foreignId('frecuencia_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->foreignId('frecuencia_id')->nullable(false)->change();
        });
    }
};
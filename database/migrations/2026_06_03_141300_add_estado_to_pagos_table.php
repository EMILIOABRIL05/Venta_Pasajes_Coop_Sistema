<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('estado', 40)->default('pendiente_validacion')->after('observaciones');
            $table->string('codigo_autorizacion', 120)->nullable()->after('estado');
            $table->timestamp('validado_at')->nullable()->after('codigo_autorizacion');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['estado', 'codigo_autorizacion', 'validado_at']);
        });
    }
};

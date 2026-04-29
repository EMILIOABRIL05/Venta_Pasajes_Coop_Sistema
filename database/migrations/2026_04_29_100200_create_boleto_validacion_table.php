<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boleto_validacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boleto_id')->constrained('boletos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('fecha_validacion');
            $table->enum('estado', ['validado', 'rechazado'])->default('validado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleto_validacion');
    }
};
</content>
<parameter name="filePath">c:\Users\Windows 11\workspace\Venta_Pasajes_Coop_Sistema\database\migrations\2026_04_29_100200_create_boleto_validacion_table.php
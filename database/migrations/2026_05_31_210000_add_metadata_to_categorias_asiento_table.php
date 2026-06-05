<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias_asiento', function (Blueprint $table) {
            if (! Schema::hasColumn('categorias_asiento', 'codigo')) {
                $table->string('codigo', 30)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('categorias_asiento', 'color_hex')) {
                $table->string('color_hex', 7)->default('#003366')->after('recargo');
            }

            if (! Schema::hasColumn('categorias_asiento', 'orden')) {
                $table->unsignedTinyInteger('orden')->default(1)->after('color_hex');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categorias_asiento', function (Blueprint $table) {
            if (Schema::hasColumn('categorias_asiento', 'codigo')) {
                $table->dropUnique(['codigo']);
                $table->dropColumn('codigo');
            }

            if (Schema::hasColumn('categorias_asiento', 'color_hex')) {
                $table->dropColumn('color_hex');
            }

            if (Schema::hasColumn('categorias_asiento', 'orden')) {
                $table->dropColumn('orden');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->foreignId('chofer_user_id')
                ->nullable()
                ->after('bus_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chofer_user_id');
        });
    }
};

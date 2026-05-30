<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN tipo_usuario ENUM('admin', 'oficinista', 'chofer', 'developer') DEFAULT 'oficinista'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET tipo_usuario = 'oficinista' WHERE tipo_usuario = 'developer'");

        DB::statement("ALTER TABLE users MODIFY COLUMN tipo_usuario ENUM('admin', 'oficinista', 'chofer') DEFAULT 'oficinista'");
    }
};

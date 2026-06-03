<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN tipo_usuario ENUM('admin', 'oficinista', 'chofer', 'developer') DEFAULT 'oficinista'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_tipo_usuario_check");

            DB::statement("ALTER TABLE users ADD CONSTRAINT users_tipo_usuario_check
                CHECK (tipo_usuario IN ('admin', 'oficinista', 'chofer', 'developer'))");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE users SET tipo_usuario = 'oficinista' WHERE tipo_usuario = 'developer'");

            DB::statement("ALTER TABLE users MODIFY COLUMN tipo_usuario ENUM('admin', 'oficinista', 'chofer') DEFAULT 'oficinista'");
        } elseif ($driver === 'pgsql') {
            DB::statement("UPDATE users SET tipo_usuario = 'oficinista' WHERE tipo_usuario = 'developer'");

            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_tipo_usuario_check");

            DB::statement("ALTER TABLE users ADD CONSTRAINT users_tipo_usuario_check
                CHECK (tipo_usuario IN ('admin', 'oficinista', 'chofer'))");
        }
    }
};

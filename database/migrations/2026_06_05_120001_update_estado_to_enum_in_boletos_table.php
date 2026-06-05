<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mapear valores existentes 'Vendido' al nuevo estado 'activo'
        DB::table('boletos')
            ->where('estado', 'Vendido')
            ->update(['estado' => 'activo']);

        // 2. Crear el tipo ENUM en PostgreSQL
        DB::statement("CREATE TYPE estado_boleto_enum AS ENUM ('activo', 'cancelado', 'usado', 'no_show')");

        // 3. Eliminar el default ANTES de cambiar el tipo (PostgreSQL no puede castear defaults automáticamente)
        DB::statement('ALTER TABLE boletos ALTER COLUMN estado DROP DEFAULT');

        // 4. Convertir la columna al nuevo tipo ENUM
        DB::statement('ALTER TABLE boletos ALTER COLUMN estado TYPE estado_boleto_enum USING estado::estado_boleto_enum');

        // 5. Establecer el nuevo default
        DB::statement("ALTER TABLE boletos ALTER COLUMN estado SET DEFAULT 'activo'");
    }

    public function down(): void
    {
        // Revertir a string simple con default original
        DB::statement('ALTER TABLE boletos ALTER COLUMN estado DROP DEFAULT');
        DB::statement('ALTER TABLE boletos ALTER COLUMN estado TYPE VARCHAR(255) USING estado::VARCHAR(255)');
        DB::statement("ALTER TABLE boletos ALTER COLUMN estado SET DEFAULT 'Vendido'");
        DB::statement('DROP TYPE IF EXISTS estado_boleto_enum');
    }
};

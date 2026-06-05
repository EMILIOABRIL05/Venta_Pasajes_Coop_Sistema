<?php

namespace Database\Seeders;

use App\Models\CategoriaAsiento;
use Illuminate\Database\Seeder;

class CategoriaAsientoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'codigo' => 'ESTANDAR',
                'nombre' => 'Estandar',
                'recargo' => 0.00,
                'color_hex' => '#003366',
                'orden' => 1,
                'es_default' => true,
                'descripcion' => 'Asiento base sin recargo.',
            ],
            [
                'codigo' => 'PREFERENCIAL',
                'nombre' => 'Preferencial',
                'recargo' => 0.50,
                'color_hex' => '#2563EB',
                'orden' => 2,
                'es_default' => false,
                'descripcion' => 'Asiento con ubicacion preferencial y recargo bajo.',
            ],
            [
                'codigo' => 'VIP',
                'nombre' => 'VIP',
                'recargo' => 1.50,
                'color_hex' => '#CC0000',
                'orden' => 3,
                'es_default' => false,
                'descripcion' => 'Asiento con mayor comodidad y recargo superior.',
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaAsiento::updateOrCreate(
                ['codigo' => $categoria['codigo']],
                $categoria
            );
        }
    }
}

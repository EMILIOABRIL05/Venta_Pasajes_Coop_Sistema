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
                'nombre' => 'Estandar',
                'recargo' => 0.00,
                'es_default' => true,
                'descripcion' => 'Asiento base sin recargo.',
            ],
            [
                'nombre' => 'Preferencial',
                'recargo' => 0.50,
                'es_default' => false,
                'descripcion' => 'Asiento con ubicacion preferencial y recargo bajo.',
            ],
            [
                'nombre' => 'Premium',
                'recargo' => 1.00,
                'es_default' => false,
                'descripcion' => 'Asiento con mayor comodidad y recargo superior.',
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaAsiento::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}

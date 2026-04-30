<?php

namespace Database\Seeders;

use App\Models\CategoriaBus;
use Illuminate\Database\Seeder;

class CategoriaBusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Normal',
                'descripcion' => 'Servicio estándar para rutas de uso frecuente.',
            ],
            [
                'nombre' => 'VIP',
                'descripcion' => 'Servicio con mayor confort y mejores acabados.',
            ],
            [
                'nombre' => 'Ejecutivo',
                'descripcion' => 'Servicio premium para viajes largos con mayor comodidad.',
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaBus::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                ['descripcion' => $categoria['descripcion']]
            );
        }
    }
}
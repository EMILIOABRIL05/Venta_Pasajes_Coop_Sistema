<?php

namespace Database\Seeders;

use App\Models\Ruta;
use App\Models\Frecuencia;
use Illuminate\Database\Seeder;

class FrecuenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rutas = Ruta::all();

        foreach ($rutas as $ruta) {
            Frecuencia::create([
                'ruta_id' => $ruta->id,
                'hora_salida' => '08:00',
            ]);

            Frecuencia::create([
                'ruta_id' => $ruta->id,
                'hora_salida' => '10:30',
            ]);
        }
    }
}

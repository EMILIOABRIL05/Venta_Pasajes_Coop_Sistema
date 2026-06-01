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

        $horarios = [
            '06:00',
            '08:30',
            '12:00',
            '15:30',
        ];

        foreach ($rutas as $ruta) {
            foreach ($horarios as $hora) {
                Frecuencia::updateOrCreate(
                    [
                        'ruta_id' => $ruta->id,
                        'hora_salida' => $hora,
                    ],
                    []
                );
            }
        }
    }
}

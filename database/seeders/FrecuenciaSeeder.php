<?php

namespace Database\Seeders;

use App\Models\Frecuencia;
use App\Models\Ruta;
use Illuminate\Database\Seeder;

class FrecuenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rutas = Ruta::query()
            ->with(['origen', 'destino'])
            ->get();

        foreach ($rutas as $ruta) {
            foreach ($this->horariosParaRuta($ruta) as $hora) {
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

    private function horariosParaRuta(Ruta $ruta): array
    {
        $origen = $ruta->origen?->ciudad;
        $destino = $ruta->destino?->ciudad;

        return match ("{$origen}-{$destino}") {
            'Ambato-Quito' => ['05:30', '07:00', '10:30', '14:00', '18:00'],
            'Quito-Ambato' => ['06:00', '09:30', '13:00', '16:30', '20:00'],
            'Ambato-Guayaquil' => ['06:15', '12:30', '22:00'],
            'Guayaquil-Ambato' => ['07:00', '13:00', '22:30'],
            'Ambato-Banos' => ['06:00', '08:00', '10:00', '12:00', '15:00', '18:00'],
            'Banos-Ambato' => ['07:00', '09:00', '11:00', '14:00', '16:30', '19:00'],
            'Ambato-Puyo' => ['06:30', '10:00', '14:30', '18:30'],
            'Puyo-Ambato' => ['05:45', '09:30', '13:30', '17:30'],
            'Ambato-Tena' => ['06:00', '13:00'],
            'Tena-Ambato' => ['07:00', '14:00'],
            default => ['06:30', '12:30', '17:30'],
        };
    }
}

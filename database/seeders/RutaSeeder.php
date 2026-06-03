<?php

namespace Database\Seeders;

use App\Models\Parada;
use App\Models\Ruta;
use Illuminate\Database\Seeder;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paradas = Parada::query()
            ->pluck('id', 'nombre');

        $rutas = [
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Terrestre Quitumbe', 'precio_base' => 6.00, 'tiempo_estimado_minutos' => 150],
            ['origen' => 'Terminal Terrestre Quitumbe', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 6.00, 'tiempo_estimado_minutos' => 150],
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Terrestre Guayaquil', 'precio_base' => 15.00, 'tiempo_estimado_minutos' => 360],
            ['origen' => 'Terminal Terrestre Guayaquil', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 15.00, 'tiempo_estimado_minutos' => 360],
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Terrestre Banos', 'precio_base' => 2.50, 'tiempo_estimado_minutos' => 50],
            ['origen' => 'Terminal Terrestre Banos', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 2.50, 'tiempo_estimado_minutos' => 50],
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Terrestre Puyo', 'precio_base' => 4.50, 'tiempo_estimado_minutos' => 120],
            ['origen' => 'Terminal Terrestre Puyo', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 4.50, 'tiempo_estimado_minutos' => 120],
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Terrestre Tena', 'precio_base' => 6.50, 'tiempo_estimado_minutos' => 180],
            ['origen' => 'Terminal Terrestre Tena', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 6.50, 'tiempo_estimado_minutos' => 180],
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Latacunga', 'precio_base' => 1.50, 'tiempo_estimado_minutos' => 45],
            ['origen' => 'Terminal Latacunga', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 1.50, 'tiempo_estimado_minutos' => 45],
            ['origen' => 'Terminal Terrestre Ambato', 'destino' => 'Terminal Salcedo', 'precio_base' => 1.25, 'tiempo_estimado_minutos' => 35],
            ['origen' => 'Terminal Salcedo', 'destino' => 'Terminal Terrestre Ambato', 'precio_base' => 1.25, 'tiempo_estimado_minutos' => 35],
        ];

        foreach ($rutas as $ruta) {
            $origenId = $paradas[$ruta['origen']] ?? null;
            $destinoId = $paradas[$ruta['destino']] ?? null;

            if (! $origenId || ! $destinoId) {
                continue;
            }

            Ruta::updateOrCreate(
                [
                    'origen_id' => $origenId,
                    'destino_id' => $destinoId,
                ],
                [
                    'precio_base' => $ruta['precio_base'],
                    'tiempo_estimado_minutos' => $ruta['tiempo_estimado_minutos'],
                ]
            );
        }
    }
}

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
        $ambato = Parada::where('nombre', 'Terminal Terrestre Ambato')->first();
        $latacunga = Parada::where('nombre', 'Terminal Latacunga')->first();

        if ($ambato && $latacunga) {
            Ruta::create([
                'origen_id' => $ambato->id,
                'destino_id' => $latacunga->id,
                'precio_base' => 1.50,
                'tiempo_estimado_minutos' => 45,
            ]);

            Ruta::create([
                'origen_id' => $latacunga->id,
                'destino_id' => $ambato->id,
                'precio_base' => 1.50,
                'tiempo_estimado_minutos' => 45,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Parada;
use Illuminate\Database\Seeder;

class ParadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paradas = [
            ['nombre' => 'Terminal Terrestre Ambato', 'ciudad' => 'Ambato'],
            ['nombre' => 'Terminal Terrestre Quitumbe', 'ciudad' => 'Quito'],
            ['nombre' => 'Terminal Terrestre Guayaquil', 'ciudad' => 'Guayaquil'],
            ['nombre' => 'Terminal Terrestre Banos', 'ciudad' => 'Banos'],
            ['nombre' => 'Terminal Terrestre Puyo', 'ciudad' => 'Puyo'],
            ['nombre' => 'Terminal Terrestre Tena', 'ciudad' => 'Tena'],
            ['nombre' => 'Terminal Latacunga', 'ciudad' => 'Latacunga'],
            ['nombre' => 'Terminal Salcedo', 'ciudad' => 'Salcedo'],
        ];

        foreach ($paradas as $parada) {
            Parada::updateOrCreate(
                ['nombre' => $parada['nombre']],
                $parada
            );
        }
    }
}

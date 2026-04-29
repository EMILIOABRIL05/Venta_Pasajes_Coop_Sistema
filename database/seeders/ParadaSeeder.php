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
        Parada::create([
            'nombre' => 'Terminal Terrestre Ambato',
            'ciudad' => 'Ambato',
        ]);

        Parada::create([
            'nombre' => 'Terminal Latacunga',
            'ciudad' => 'Latacunga',
        ]);
    }
}

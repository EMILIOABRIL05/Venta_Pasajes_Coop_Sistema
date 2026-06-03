<?php

namespace Database\Seeders;

use App\Models\Asiento;
use App\Models\Bus;
use Illuminate\Database\Seeder;

class AsientoSeeder extends Seeder
{
    public function run(): void
    {
        $buses = Bus::query()->orderBy('id')->get();

        foreach ($buses as $bus) {
            $totalAsientos = (int) ($bus->numero_asientos ?? 0);

            if ($totalAsientos <= 0) {
                continue;
            }

            for ($numero = 1; $numero <= $totalAsientos; $numero++) {
                $categoria = $numero <= 4 || $numero % 12 === 0 ? 'vip' : 'estandar';

                Asiento::updateOrCreate(
                    [
                        'bus_id' => $bus->id,
                        'numero' => $numero,
                    ],
                    [
                        'categoria' => $categoria,
                    ]
                );
            }
        }
    }
}
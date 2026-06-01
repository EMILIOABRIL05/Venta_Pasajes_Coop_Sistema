<?php

namespace Database\Seeders;

use App\Models\Bus;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $buses = [
            [
                'placa' => 'TST-1001',
                'marca_chasis' => 'Hino',
                'carroceria' => 'Imce',
                'anio' => 2022,
                'filas' => 10,
                'estado' => 'disponible',
            ],
            [
                'placa' => 'TST-2001',
                'marca_chasis' => 'Mercedes Benz',
                'carroceria' => 'Marcopolo',
                'anio' => 2021,
                'filas' => 9,
                'estado' => 'disponible',
            ],
        ];

        foreach ($buses as $busData) {
            $filas = (int) $busData['filas'];
            $mapa = Bus::generarEstructuraAsientos($filas, true);

            Bus::updateOrCreate(
                ['placa' => $busData['placa']],
                [
                    'marca_chasis' => $busData['marca_chasis'],
                    'carroceria' => $busData['carroceria'],
                    'anio' => $busData['anio'],
                    'numero_asientos' => Bus::calcularCapacidad($filas, true),
                    'mapa_asientos' => $mapa,
                    'estado' => $busData['estado'],
                    'foto' => null,
                ]
            );
        }
    }
}

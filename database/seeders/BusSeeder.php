<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\CategoriaAsiento;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = CategoriaAsiento::query()->orderBy('orden')->get();
        $categoriaDefault = $categorias->firstWhere('es_default', true) ?? $categorias->first();
        $categoriaPreferencial = $categorias->firstWhere('nombre', 'Preferencial');
        $categoriaVip = $categorias->firstWhere('nombre', 'VIP');

        $buses = [
            [
                'placa' => 'TAA-1001',
                'marca_chasis' => 'Hino',
                'carroceria' => 'Imce',
                'anio' => 2022,
                'filas' => 10,
                'estado' => 'disponible',
                'categorias_por_asiento' => [
                    [
                        'categoria_id' => $categoriaPreferencial?->id,
                        'asientos' => [1, 2, 3, 4],
                    ],
                    [
                        'categoria_id' => $categoriaVip?->id,
                        'asientos' => [5, 6],
                    ],
                ],
            ],
            [
                'placa' => 'TAA-2001',
                'marca_chasis' => 'Mercedes Benz',
                'carroceria' => 'Marcopolo',
                'anio' => 2021,
                'filas' => 9,
                'estado' => 'disponible',
                'categorias_por_asiento' => [
                    [
                        'categoria_id' => $categoriaPreferencial?->id,
                        'asientos' => [1, 2],
                    ],
                ],
            ],
            [
                'placa' => 'TAA-3001',
                'marca_chasis' => 'Scania',
                'carroceria' => 'Busscar',
                'anio' => 2023,
                'filas' => 10,
                'estado' => 'disponible',
                'categorias_por_asiento' => [
                    [
                        'categoria_id' => $categoriaPreferencial?->id,
                        'asientos' => [1, 2, 3, 4, 7, 8],
                    ],
                    [
                        'categoria_id' => $categoriaVip?->id,
                        'asientos' => [5, 6, 9, 10],
                    ],
                ],
            ],
        ];

        foreach ($buses as $busData) {
            $filas = (int) $busData['filas'];
            $mapa = Bus::generarMapaAsientosCategorizado(
                $filas,
                true,
                $categoriaDefault,
                $categorias,
                $busData['categorias_por_asiento']
            );

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

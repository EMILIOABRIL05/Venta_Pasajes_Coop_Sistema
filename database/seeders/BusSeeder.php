<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\CategoriaAsiento;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = CategoriaAsiento::query()->orderBy('recargo')->get();
        $categoriaDefault = $categorias->firstWhere('es_default', true) ?? $categorias->first();
        $categoriaPreferencial = $categorias->firstWhere('nombre', 'Preferencial');
        $categoriaPremium = $categorias->firstWhere('nombre', 'Premium');

        $buses = [
            [
                'placa' => 'TST-1001',
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
                        'categoria_id' => $categoriaPremium?->id,
                        'asientos' => [5, 6],
                    ],
                ],
            ],
            [
                'placa' => 'TST-2001',
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
        ];

        foreach ($buses as $busData) {
            $filas = (int) $busData['filas'];
            $mapa = Bus::generarEstructuraAsientos($filas, true);

            $mapa['categoria_defecto_id'] = $categoriaDefault?->id;
            $mapa['categorias_asiento'] = $categorias->map(static function (CategoriaAsiento $categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'recargo' => (float) $categoria->recargo,
                    'es_default' => $categoria->es_default,
                ];
            })->values()->all();
            $mapa['asientos_categoria'] = $busData['categorias_por_asiento'];

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

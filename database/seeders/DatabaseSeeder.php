<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ParadaSeeder::class,
            RutaSeeder::class,
            FrecuenciaSeeder::class,
            CategoriaAsientoSeeder::class,
            BusSeeder::class,
            AsientoSeeder::class,
            RolesAndPermissionsSeeder::class,
            TestUsersSeeder::class,
        ]);
    }
}

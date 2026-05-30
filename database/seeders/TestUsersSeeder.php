<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Prueba',
                'email' => 'admin@cooperativa.test',
                'password' => Hash::make('Admin12345!'),
                'cedula' => '1900000001',
                'telefono' => '0987654321',
                'fecha_nacimiento' => '1980-01-15',
                'tipo_usuario' => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Oficinista Prueba',
                'email' => 'oficinista@cooperativa.test',
                'password' => Hash::make('Oficina12345!'),
                'cedula' => '1900000002',
                'telefono' => '0987654322',
                'fecha_nacimiento' => '1990-02-20',
                'tipo_usuario' => 'oficinista',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Chofer Prueba',
                'email' => 'chofer@cooperativa.test',
                'password' => Hash::make('Chofer12345!'),
                'cedula' => '1900000003',
                'telefono' => '0987654323',
                'fecha_nacimiento' => '1985-03-25',
                'tipo_usuario' => 'chofer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Developer Prueba',
                'email' => 'developer@cooperativa.test',
                'password' => Hash::make('password123'),
                'cedula' => '1900000004',
                'telefono' => '0987654324',
                'fecha_nacimiento' => '1995-06-10',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            $user->assignRole($userData['tipo_usuario']);
        }
    }
}

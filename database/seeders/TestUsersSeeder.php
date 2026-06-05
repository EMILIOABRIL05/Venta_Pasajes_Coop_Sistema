<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
            // ─── Real Team Members ────────────────────────────────────────────
            [
                'name' => 'Kevin Velasco',
                'email' => 'kevin@cooperativa.test',
                'password' => Hash::make('password'),
                'cedula' => '1712345671',
                'telefono' => '0991111111',
                'fecha_nacimiento' => '1998-04-12',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emilio Abril',
                'email' => 'emilio@cooperativa.test',
                'password' => Hash::make('password'),
                'cedula' => '1712345672',
                'telefono' => '0992222222',
                'fecha_nacimiento' => '1997-08-20',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Luis Miranda',
                'email' => 'luis@cooperativa.test',
                'password' => Hash::make('password'),
                'cedula' => '1712345673',
                'telefono' => '0993333333',
                'fecha_nacimiento' => '1999-01-15',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Anthony Semblantes',
                'email' => 'anthony@cooperativa.test',
                'password' => Hash::make('password'),
                'cedula' => '1712345674',
                'telefono' => '0994444444',
                'fecha_nacimiento' => '1998-11-05',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Manuel Cusme',
                'email' => 'manuel@cooperativa.test',
                'password' => Hash::make('password'),
                'cedula' => '1712345675',
                'telefono' => '0995555555',
                'fecha_nacimiento' => '1996-06-30',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Manolo Garcia',
                'email' => 'manolo@cooperativa.test',
                'password' => Hash::make('password'),
                'cedula' => '1712345676',
                'telefono' => '0996666666',
                'fecha_nacimiento' => '1997-03-18',
                'tipo_usuario' => 'developer',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['deleted_at' => null])
            );

            $user->forceFill([
                'password' => $userData['password'],
            ])->save();

            Role::firstOrCreate(['name' => $userData['tipo_usuario']]);
            $user->syncRoles([$userData['tipo_usuario']]);
        }

        $this->command->info(count($users) . ' usuarios creados exitosamente con sus roles asignados.');
    }
}

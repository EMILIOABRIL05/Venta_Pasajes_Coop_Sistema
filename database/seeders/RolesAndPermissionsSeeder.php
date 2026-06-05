<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos base según especificación
        $permissions = [
            'manage_buses',
            'manage_frecuencias',
            'sell_boletos',
            'validate_comprobantes',
            'scan_qr',
            'manage_users',
            'view_reports',
            'manage_config',
            'sell_onboard'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles y asignación de permisos
        $rolesPermissions = [
            'admin' => $permissions,
            'developer' => [
                'manage_config',
                'view_reports',
                'scan_qr',
            ],
            'oficinista' => [
                'sell_boletos',
                'validate_comprobantes',
                'manage_buses'
            ],
            'chofer' => [
                'scan_qr',
                'sell_onboard'
            ],
            'cliente' => []
        ];

        foreach ($rolesPermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }
    }
}

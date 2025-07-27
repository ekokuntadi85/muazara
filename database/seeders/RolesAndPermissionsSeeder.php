<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-dashboard',
            'manage-products',
            'manage-purchases',
            'delete-purchases',
            'manage-sales',
            'delete-sales',
            'view-reports',
            'manage-users',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign existing permissions
        $roleOwner = Role::firstOrCreate(['name' => 'owner']);
        $roleOwner->givePermissionTo($permissions);

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo([
            'view-dashboard',
            'manage-products',
            'manage-purchases',
            'manage-sales',
            'view-reports',
            'manage-users',
            'manage-settings',
        ]);

        $roleKasir = Role::firstOrCreate(['name' => 'kasir']);
        $roleKasir->givePermissionTo('manage-sales');
    }
}
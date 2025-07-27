<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            PurchaseSeeder::class,
        ]);

        // Create users with different roles
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            ['name' => 'Owner User', 'password' => bcrypt('password')]
        );
        $owner->assignRole('owner');

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );
        $admin->assignRole('admin');

        $kasir = User::firstOrCreate(
            ['email' => 'kasir@example.com'],
            ['name' => 'Kasir User', 'password' => bcrypt('password')]
        );
        $kasir->assignRole('kasir');
    }
}
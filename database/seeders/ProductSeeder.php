<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure categories and units are seeded first
        $this->call(CategorySeeder::class);
        $this->call(UnitSeeder::class);

        Product::factory()->count(50)->create();
    }
}

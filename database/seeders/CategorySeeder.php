<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'Obat Bebas'], ['description' => 'Obat yang dapat dibeli tanpa resep dokter.']);
        Category::firstOrCreate(['name' => 'Obat Resep'], ['description' => 'Obat yang memerlukan resep dokter.']);
        // Add more categories if needed, up to 2 as requested
        Category::factory()->count(max(0, 2 - Category::count()))->create();
    }
}

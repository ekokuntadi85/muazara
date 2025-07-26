<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Tablet', 'short_name' => 'tab'],
            ['name' => 'Kapsul', 'short_name' => 'kps'],
            ['name' => 'Botol', 'short_name' => 'btl'],
            ['name' => 'Strip', 'short_name' => 'str'],
            ['name' => 'Tube', 'short_name' => 'tbe'],
        ];

        foreach ($units as $unitData) {
            Unit::firstOrCreate(['name' => $unitData['name']], $unitData);
        }
    }
}

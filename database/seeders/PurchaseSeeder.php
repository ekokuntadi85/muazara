<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\ProductBatch;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Purchase::factory()->count(10)->create()->each(function ($purchase) {
            // Attach 1 to 5 product batches to each purchase
            $products = Product::inRandomOrder()->limit(rand(1, 5))->get();
            foreach ($products as $product) {
                ProductBatch::factory()->create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'purchase_price' => $product->selling_price * (rand(70, 90) / 100), // 70-90% of selling price
                    'stock' => rand(10, 100),
                    'expiration_date' => now()->addDays(rand(30, 365)),
                ]);
            }
        });
    }
}
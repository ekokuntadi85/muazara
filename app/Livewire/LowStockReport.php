<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class LowStockReport extends Component
{
    public $stock_threshold = 10;

    public function render()
    {
        $products = Product::with('productBatches')
                            ->get()
                            ->filter(function ($product) {
                                return $product->productBatches->sum('stock') <= $this->stock_threshold;
                            });

        return view('livewire.low-stock-report', compact('products'));
    }
}
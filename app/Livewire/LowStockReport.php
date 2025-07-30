<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

use Livewire\WithPagination;

class LowStockReport extends Component
{
    use WithPagination;
    public $stock_threshold = 10;

    public function render()
    {
        $products = Product::with('productBatches')
            ->withSum('productBatches as total_stock', 'stock')
            ->having('total_stock', '<', $this->stock_threshold)
            ->orderBy('total_stock', 'asc')
            ->paginate(10);

        return view('livewire.low-stock-report', compact('products'));
    }

    public function updatingStockThreshold()
    {
        $this->resetPage();
    }
}
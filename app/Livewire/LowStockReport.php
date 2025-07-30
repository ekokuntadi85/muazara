<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class LowStockReport extends Component
{
    use WithPagination;
    public $stock_threshold = 10;

    public function render()
    {
        $products = Product::withSum('productBatches as total_stock', 'stock')
            ->whereRaw('COALESCE((SELECT SUM(stock) FROM product_batches WHERE products.id = product_batches.product_id), 0) < ?', [$this->stock_threshold])
            ->orderBy('total_stock', 'asc')
            ->paginate(10);

        return view('livewire.low-stock-report', compact('products'));
    }

    public function updatingStockThreshold()
    {
        $this->resetPage();
    }
}

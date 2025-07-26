<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductShow extends Component
{
    public $product;

    public function mount(Product $product)
    {
        $this->product = $product->load(['category', 'unit', 'productBatches.purchase.supplier']);
    }

    public function deleteProduct()
    {
        DB::transaction(function () {
            // Delete related product batches first
            $this->product->productBatches()->delete();
            // Then delete the product
            $this->product->delete();
        });

        session()->flash('message', 'Produk berhasil dihapus.');
        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product-show');
    }
}
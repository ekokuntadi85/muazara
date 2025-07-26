<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductManager extends Component
{
    public $search = '';

    public function render()
    {
        $products = Product::with(['category', 'unit'])
                            ->where(function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                            })
                            ->latest()
                            ->get();

        return view('livewire.product-manager', compact('products'));
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('message', 'Produk berhasil dihapus.');
    }
}

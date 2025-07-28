<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $products = Product::with(['category', 'unit'])
                            ->where(function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                            })
                            ->latest()
                            ->paginate(10);

        return view('livewire.product-manager', compact('products'));
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('message', 'Produk berhasil dihapus.');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}

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
        $products = Product::with(['category', 'baseUnit', 'productUnits'])
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
        \Illuminate\Support\Facades\Log::info('ProductManager: Delete method called for product ID: ' . $id);

        $product = Product::withCount(['productBatches', 'transactionDetails'])->find($id);

        if ($product->product_batches_count > 0 || $product->transaction_details_count > 0) {
            session()->flash('error', 'Produk tidak dapat dihapus karena memiliki riwayat transaksi pembelian atau penjualan.');
            return;
        }

        if ($product->delete()) {
            session()->flash('message', 'Produk berhasil dihapus.');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}

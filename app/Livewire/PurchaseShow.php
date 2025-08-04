<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class PurchaseShow extends Component
{
    public $purchase;

    public function mount(Purchase $purchase)
    {
        $this->purchase = $purchase->load(['supplier', 'productBatches.product.baseUnit', 'productBatches.productUnit']);
    }

    public function markAsPaid()
    {
        $this->purchase->update(['payment_status' => 'paid']);
        session()->flash('message', 'Pembelian berhasil ditandai lunas.');
    }

    public function deletePurchase()
    {
        DB::transaction(function () {
            $this->purchase->productBatches()->delete(); // Delete related product batches
            $this->purchase->delete(); // Delete the purchase record
        });

        session()->flash('message', 'Pembelian berhasil dihapus.');
        return redirect()->route('purchases.index');
    }

    public function render()
    {
        return view('livewire.purchase-show');
    }
}

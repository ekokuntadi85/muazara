<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

use Livewire\Attributes\Title;

#[Title('Lihat Pembelian')]
class PurchaseShow extends Component
{
    public $purchase;

    public function mount(Purchase $purchase)
    {
        $this->purchase = $purchase->load(['supplier', 'productBatches.product.baseUnit', 'productBatches.productUnit']);

        // Eager load initial stock movements to solve N+1 problem
        $batchIds = $this->purchase->productBatches->pluck('id');
        $stockMovements = StockMovement::whereIn('product_batch_id', $batchIds)
                                       ->where('type', 'PB')
                                       ->get()
                                       ->keyBy('product_batch_id');

        // Attach the original purchased stock quantity and calculated prices to each batch
        foreach ($this->purchase->productBatches as $batch) {
            $movement = $stockMovements->get($batch->id);
            $batch->original_stock = $movement ? $movement->quantity : 0; // This is in BASE units

            if ($batch->productUnit && $batch->productUnit->conversion_factor > 0) {
                // Final Fix: The purchase_price from DB is correct for the purchased unit. Only quantity needs conversion.
                $batch->display_purchase_price = $batch->purchase_price;
                $batch->original_input_quantity = $batch->original_stock / $batch->productUnit->conversion_factor;
                $batch->display_unit_name = $batch->productUnit->name;
            } else {
                // Fallback for base unit purchases or data issues
                $batch->display_purchase_price = $batch->purchase_price;
                $batch->original_input_quantity = $batch->original_stock;
                $batch->display_unit_name = $batch->product->baseUnit->name ?? 'units';
            }
        }
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

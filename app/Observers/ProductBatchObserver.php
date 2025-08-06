<?php

namespace App\Observers;

use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ProductBatchObserver
{
    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $productBatch): void
    {
        // Record initial stock movement for purchase
        StockMovement::create([
            'product_batch_id' => $productBatch->id,
            'type' => 'PB',
            'quantity' => $productBatch->stock,
            'remarks' => 'Pembelian awal',
        ]);

        if ($productBatch->purchase) {
            $this->updatePurchaseTotalPrice($productBatch->purchase);
        }
    }

    /**
     * Handle the ProductBatch "updated" event.
     */
    public function updated(ProductBatch $productBatch): void
    {
        // Removed the ADJ stock movement creation here.
        // Stock movements for sales (PJ) and opname (OP) will be handled explicitly in their respective modules.
        if ($productBatch->purchase) {
            $this->updatePurchaseTotalPrice($productBatch->purchase);
        }
    }

    /**
     * Handle the ProductBatch "deleted" event.
     */
    public function deleted(ProductBatch $productBatch): void
    {
        // Record stock movement for deletion (negative quantity)
        StockMovement::create([
            'product_batch_id' => $productBatch->id,
            'type' => 'DEL', // Deletion
            'quantity' => -$productBatch->stock, // Record current stock as negative
            'remarks' => 'Batch dihapus',
        ]);

        if ($productBatch->purchase) {
            $this->updatePurchaseTotalPrice($productBatch->purchase);
        }
    }

    /**
     * Handle the ProductBatch "restored" event.
     */
    public function restored(ProductBatch $productBatch): void
    {
        // Record stock movement for restoration
        StockMovement::create([
            'product_batch_id' => $productBatch->id,
            'type' => 'RES', // Restoration
            'quantity' => $productBatch->stock,
            'remarks' => 'Batch dikembalikan',
        ]);

        if ($productBatch->purchase) {
            $this->updatePurchaseTotalPrice($productBatch->purchase);
        }
    }

    /**
     * Handle the ProductBatch "force deleted" event.
     * This event is not typically used for stock tracking as it bypasses soft deletes.
     */
    public function forceDeleted(ProductBatch $productBatch): void
    {
        // No specific stock movement for force delete, as it's usually a permanent removal
        // and 'deleted' event should cover the stock reduction.
        if ($productBatch->purchase) {
            $this->updatePurchaseTotalPrice($productBatch->purchase);
        }
    }

    /**
     * Update the total price of the purchase.
     */
    protected function updatePurchaseTotalPrice(?Purchase $purchase)
    {
        if (!$purchase) {
            return;
        }
        $totalPrice = $purchase->productBatches()->sum(DB::raw('purchase_price * stock'));
        $purchase->total_price = $totalPrice;
        $purchase->saveQuietly(); // Use saveQuietly to prevent triggering other events
    }
}

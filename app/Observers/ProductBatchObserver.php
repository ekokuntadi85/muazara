<?php

namespace App\Observers;

use App\Models\ProductBatch;
use App\Models\Purchase;

class ProductBatchObserver
{
    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $productBatch): void
    {
        $this->updatePurchaseTotalPrice($productBatch->purchase);
    }

    /**
     * Handle the ProductBatch "updated" event.
     */
    public function updated(ProductBatch $productBatch): void
    {
        $this->updatePurchaseTotalPrice($productBatch->purchase);
    }

    /**
     * Handle the ProductBatch "deleted" event.
     */
    public function deleted(ProductBatch $productBatch): void
    {
        $this->updatePurchaseTotalPrice($productBatch->purchase);
    }

    /**
     * Handle the ProductBatch "restored" event.
     */
    public function restored(ProductBatch $productBatch): void
    {
        $this->updatePurchaseTotalPrice($productBatch->purchase);
    }

    /**
     * Handle the ProductBatch "force deleted" event.
     */
    public function forceDeleted(ProductBatch $productBatch): void
    {
        $this->updatePurchaseTotalPrice($productBatch->purchase);
    }

    /**
     * Update the total price of the purchase.
     */
    protected function updatePurchaseTotalPrice(Purchase $purchase)
    {
        $totalPrice = $purchase->productBatches()->sum(\DB::raw('purchase_price * stock'));
        $purchase->total_price = $totalPrice;
        $purchase->saveQuietly(); // Use saveQuietly to prevent triggering other events
    }
}

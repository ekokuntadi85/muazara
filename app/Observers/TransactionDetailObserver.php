<?php

namespace App\Observers;

use App\Models\TransactionDetail;
use App\Models\StockMovement;

class TransactionDetailObserver
{
    /**
     * Handle the TransactionDetail "created" event.
     */
    public function created(TransactionDetail $detail): void
    {
        $this->decrementStock($detail);
    }

    /**
     * Handle the TransactionDetail "deleted" event.
     */
    public function deleted(TransactionDetail $detail): void
    {
        $this->incrementStock($detail);
    }

    /**
     * Decrements stock from the oldest batches first (FEFO).
     */
    protected function decrementStock(TransactionDetail $detail): void
    {
        $quantityToDecrement = $detail->quantity;
        $product = $detail->product;

        $batches = $product->productBatches()
                           ->where('stock', '>', 0)
                           ->orderBy('expiration_date', 'asc')
                           ->get();

        foreach ($batches as $batch) {
            if ($quantityToDecrement <= 0) {
                break;
            }

            $stockToTake = min($batch->stock, $quantityToDecrement);
            
            if ($stockToTake > 0) {
                $batch->decrement('stock', $stockToTake);

                StockMovement::create([
                    'product_batch_id' => $batch->id,
                    'type' => 'PJ', // Penjualan
                    'quantity' => -$stockToTake,
                    'remarks' => 'Penjualan #' . $detail->transaction->invoice_number,
                ]);
            }
            
            $quantityToDecrement -= $stockToTake;
        }
    }

    /**
     * Increments stock when a transaction detail is deleted.
     */
    protected function incrementStock(TransactionDetail $detail): void
    {
        $quantityToIncrement = $detail->quantity;
        $product = $detail->product;

        // Add stock back to the first available batch.
        $batch = $product->productBatches()->orderBy('expiration_date', 'asc')->first();

        if ($batch) {
            $batch->increment('stock', $quantityToIncrement);

            StockMovement::create([
                'product_batch_id' => $batch->id,
                'type' => 'R-PJ', // Retur Penjualan
                'quantity' => $quantityToIncrement,
                'remarks' => 'Retur/Hapus Penjualan #' . $detail->transaction->invoice_number,
            ]);
        }
    }
}

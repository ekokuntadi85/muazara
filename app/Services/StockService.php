<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailBatch;

class StockService
{
    public function decrementStock(TransactionDetail $detail): void
    {
        $quantityToDecrement = $detail->quantity;
        $product = $detail->product;

        $batches = $product->productBatches()
                           ->where('stock', '>', 0)
                           ->orderBy('expiration_date', 'asc')
                           ->lockForUpdate()
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

                TransactionDetailBatch::create([
                    'transaction_detail_id' => $detail->id,
                    'product_batch_id' => $batch->id,
                    'quantity' => $stockToTake,
                ]);
            }

            $quantityToDecrement -= $stockToTake;
        }
    }

    public function incrementStock(TransactionDetail $detail): void
    {
        foreach ($detail->transactionDetailBatches as $tdb) {
            $batch = $tdb->productBatch;
            if ($batch) {
                $batch->increment('stock', $tdb->quantity);

                StockMovement::create([
                    'product_batch_id' => $batch->id,
                    'type' => 'R-PJ', // Retur Penjualan
                    'quantity' => $tdb->quantity,
                    'remarks' => 'Retur/Hapus Penjualan #' . $detail->transaction->invoice_number,
                ]);
            }
        }
    }
}

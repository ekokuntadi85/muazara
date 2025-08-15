<?php

namespace App\Observers;

use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\TransactionDetailBatch;
use App\Services\StockService;

class TransactionDetailObserver
{
    /**
     * Handle the TransactionDetail "created" event.
     */
    public function created(TransactionDetail $detail): void
    {
        (new StockService())->decrementStock($detail);
    }

    /**
     * Handle the TransactionDetail "deleted" event.
     */
    public function deleted(TransactionDetail $detail): void
    {
        (new StockService())->incrementStock($detail);
    }
}

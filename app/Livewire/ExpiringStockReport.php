<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductBatch;
use Carbon\Carbon;

class ExpiringStockReport extends Component
{
    public $expiry_threshold_months = 2;

    public function render()
    {
        $thresholdDate = Carbon::now()->addMonths((int)$this->expiry_threshold_months)->endOfDay();

        $productBatches = ProductBatch::with(['product', 'purchase.supplier'])
                                    ->where('stock', '>', 0)
                                    ->where('expiration_date', '<=', $thresholdDate)
                                    ->orderBy('expiration_date', 'asc')
                                    ->get();

        return view('livewire.expiring-stock-report', compact('productBatches'));
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;

class PurchaseManager extends Component
{
    public $search = '';
    public $filterStatus = 'all';

    public function render()
    {
        $purchases = Purchase::with(['supplier'])
                                ->where(function ($query) {
                                    $query->where('invoice_number', 'like', '%' . $this->search . '%')
                                          ->orWhereHas('supplier', function ($query) {
                                              $query->where('name', 'like', '%' . $this->search . '%');
                                          });
                                })
                                ->when($this->filterStatus !== 'all', function ($query) {
                                    $query->where('payment_status', $this->filterStatus);
                                })
                                ->latest()
                                ->get();

        return view('livewire.purchase-manager', compact('purchases'));
    }
}

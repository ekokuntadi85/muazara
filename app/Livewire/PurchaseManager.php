<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;

class PurchaseManager extends Component
{
    public $search = '';

    public function render()
    {
        $purchases = Purchase::with(['supplier'])
                                ->where(function ($query) {
                                    $query->where('invoice_number', 'like', '%' . $this->search . '%')
                                          ->orWhereHas('supplier', function ($query) {
                                              $query->where('name', 'like', '%' . $this->search . '%');
                                          });
                                })
                                ->latest()
                                ->get();

        return view('livewire.purchase-manager', compact('purchases'));
    }
}

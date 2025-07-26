<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class TransactionManager extends Component
{
    public $search = '';

    public function render()
    {
        $transactions = Transaction::with(['user', 'customer'])
                                    ->where(function ($query) {
                                        $query->where('type', 'like', '%' . $this->search . '%')
                                              ->orWhere('payment_status', 'like', '%' . $this->search . '%')
                                              ->orWhereHas('customer', function ($query) {
                                                  $query->where('name', 'like', '%' . $this->search . '%');
                                              });
                                    })
                                    ->latest()
                                    ->get();

        return view('livewire.transaction-manager', compact('transactions'));
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class TransactionManager extends Component
{
    public $search = '';
    public $filterType = 'all';

    public function render()
    {
        $transactions = Transaction::with(['user', 'customer'])
                                    ->where(function ($query) {
                                        $query->where('type', 'like', '%' . $this->search . '%')
                                              ->orWhere('payment_status', 'like', '%' . $this->search . '%')
                                              ->orWhere('invoice_number', 'like', '%' . $this->search . '%')
                                              ->orWhereDate('created_at', 'like', '%' . $this->search . '%')
                                              ->orWhereHas('customer', function ($query) {
                                                  $query->where('name', 'like', '%' . $this->search . '%');
                                              });
                                    })
                                    ->when($this->filterType !== 'all', function ($query) {
                                        $query->whereRaw('UPPER(type) = ?', [strtoupper($this->filterType)]);
                                    })
                                    ->latest()
                                    ->get();

        return view('livewire.transaction-manager', compact('transactions'));
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Livewire\WithPagination;

class AccountsReceivable extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'unpaid'; // Default to unpaid receivables

    public function render()
    {
        $transactions = Transaction::with('customer')
                                ->where('type', 'invoice') // Only show invoices for accounts receivable
                                ->where(function ($query) {
                                    // Apply search filter
                                    $query->where('invoice_number', 'like', '%' . $this->search . '%')
                                          ->orWhereHas('customer', function ($q) {
                                              $q->where('name', 'like', '%' . $this->search . '%');
                                          });
                                })
                                ->when($this->filterStatus !== 'all', function ($query) {
                                    // Apply status filter
                                    $query->where('payment_status', $this->filterStatus);
                                })
                                ->latest()
                                ->paginate(10);

        return view('livewire.accounts-receivable', compact('transactions'));
    }

    public function markAsPaid($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $transaction->payment_status = 'paid';
        $transaction->save();

        session()->flash('message', 'Transaksi berhasil ditandai sebagai lunas.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
}

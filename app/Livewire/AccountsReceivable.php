<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class AccountsReceivable extends Component
{
    public function render()
    {
        $receivables = Transaction::with('customer')
                                ->where('type', 'invoice')
                                ->where('payment_status', 'unpaid')
                                ->latest()
                                ->get();

        return view('livewire.accounts-receivable', compact('receivables'));
    }

    public function markAsPaid($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $transaction->payment_status = 'paid';
        $transaction->save();

        session()->flash('message', 'Transaksi berhasil ditandai sebagai lunas.');
    }
}
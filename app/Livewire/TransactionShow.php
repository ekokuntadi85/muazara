<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

use Livewire\Attributes\Title;

#[Title('Lihat Transaksi')]
class TransactionShow extends Component
{
    public $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['user', 'customer', 'transactionDetails.product']);
    }

    public function deleteTransaction()
    {
        DB::transaction(function () {
            $this->transaction->transactionDetails()->delete(); // Delete related transaction details
            $this->transaction->delete(); // Delete the transaction record
        });

        session()->flash('message', 'Transaksi berhasil dihapus.');
        return redirect()->route('transactions.index');
    }

    public function render()
    {
        return view('livewire.transaction-show');
    }
}
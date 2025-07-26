<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;

class SalesReport extends Component
{
    public $startDate;
    public $endDate;
    public $totalRevenue = 0;
    public $profitLossCode = '';
    public $totalProfitLoss = 0;
    public $showProfitLossValue = false;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $transactions = Transaction::with(['customer', 'user', 'transactionDetails.product.productBatches'])
                                ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
                                ->latest()
                                ->get();

        $this->totalRevenue = $transactions->sum('total_price');

        return view('livewire.sales-report', compact('transactions'));
    }

    public function filter()
    {
        $this->resetProfitLoss();
        // This method is empty, but Livewire will re-render the component
        // and thus re-run the render() method with updated start/end dates.
    }

    public function calculateProfitLoss()
    {
        $this->validate([
            'profitLossCode' => 'required|string|in:8485',
        ], [
            'profitLossCode.required' => 'Kode proteksi wajib diisi.',
            'profitLossCode.in' => 'Kode proteksi salah.',
        ]);

        $transactions = Transaction::with(['transactionDetails.product.productBatches'])
                                ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
                                ->get();

        $totalCOGS = 0;
        foreach ($transactions as $transaction) {
            foreach ($transaction->transactionDetails as $detail) {
                $product = $detail->product;
                if ($product && $product->productBatches->isNotEmpty()) {
                    $costPerUnit = $product->productBatches->first()->purchase_price;
                    $totalCOGS += $detail->quantity * $costPerUnit;
                }
            }
        }

        $this->totalProfitLoss = $this->totalRevenue - $totalCOGS;
        $this->showProfitLossValue = true;
    }

    public function resetProfitLoss()
    {
        $this->showProfitLossValue = false;
        $this->profitLossCode = '';
        $this->totalProfitLoss = 0;
    }
}
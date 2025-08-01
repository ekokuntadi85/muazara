<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\ProductBatch;
use App\Models\Customer;
use App\Models\Purchase;
use Carbon\Carbon;

class DashboardOverview extends Component
{
    public $salesToday = 0;
    public $visitsToday = 0;
    public $expiringProductsCount = 0;
    public $latestTransactions = [];
    public $upcomingUnpaidPurchases = [];
    public $salesChartData = ['series' => [], 'labels' => []];

    public function mount()
    {
        $today = Carbon::today();
        $sevenDaysFromNow = Carbon::now()->addDays(7);

        // Sales Today
        $this->salesToday = Transaction::whereDate('created_at', $today)->sum('total_price');

        // Visits Today (Counting each transaction)
        $this->visitsToday = Transaction::whereDate('created_at', $today)->count();

        // Expiring Products Count (e.g., within next 30 days)
        $expiringDate = Carbon::now()->addDays(30);
        $this->expiringProductsCount = ProductBatch::where('expiration_date', '<=', $expiringDate)
                                                    ->sum('stock');

        // Latest 10 Transactions
        $this->latestTransactions = Transaction::with(['customer', 'user'])
                                                ->latest()
                                                ->limit(10)
                                                ->get();

        // Upcoming Unpaid Purchases
        $this->upcomingUnpaidPurchases = Purchase::with('supplier')
                                                ->where('payment_status', 'unpaid')
                                                ->where('due_date', '<=', $sevenDaysFromNow)
                                                ->orderBy('due_date', 'asc')
                                                ->limit(5)
                                                ->get();

        $this->loadSalesChartData();
    }

    private function loadSalesChartData()
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(29);

        $sales = Transaction::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
                            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
                            ->groupBy('date')
                            ->orderBy('date', 'asc')
                            ->get()
                            ->pluck('total', 'date');

        $dates = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->put($date->format('Y-m-d'), 0);
        }

        $mergedData = $dates->merge($sales);

        $this->salesChartData = [
            'series' => $mergedData->values()->toArray(),
            'labels' => $mergedData->keys()->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-overview');
    }
}
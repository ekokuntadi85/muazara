<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Carbon\Carbon;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StockCard extends Component
{
    use WithPagination;

    public $selectedProductId; // Changed from selectedProductBatchId
    public $selectedProductName; // To display selected product name
    public $month; // Filter by month
    public $year; // Filter by year

    public $initialBalance = 0;
    public $finalBalance = 0; // New property for final balance
    public $startDate; // New property for start date of period
    public $endDate; // New property for end date of period

    public $searchProduct = ''; // Changed from searchProductBatch
    public $productResults = []; // Changed from productBatchResults

    protected $queryString = ['selectedProductId', 'month', 'year']; // Updated query string

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
        $this->updateDates(); // Initialize dates

        // If selectedProductId is present in query string, load product name
        if ($this->selectedProductId) {
            $product = Product::find($this->selectedProductId);
            if ($product) {
                $this->selectedProductName = $product->name;
            }
        }
        $this->calculateBalances(); // Calculate balances after product selection
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($this->searchProduct) >= 2) {
            $this->productResults = Product::withSum('productBatches as total_stock', 'stock')
                ->where('name', 'like', '%' . $value . '%')
                ->orWhere('sku', 'like', '%' . $value . '%')
                ->limit(10)
                ->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        $this->selectedProductId = $productId;
        $this->selectedProductName = $product->name;
        $this->searchProduct = '';
        $this->productResults = [];
        $this->resetPage();
        $this->calculateBalances(); // Recalculate balances after product selection
    }

    public function updateDates()
    {
        $this->startDate = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $this->endDate = Carbon::create($this->year, $this->month)->endOfMonth()->endOfDay();
    }

    public function calculateBalances()
    {
        if ($this->selectedProductId) {
            $this->updateDates(); // Ensure dates are updated before calculation

            // Calculate initial balance (sum of all movements for all batches of this product before startDate)
            $this->initialBalance = StockMovement::whereHas('productBatch', function($query) {
                                                $query->where('product_id', $this->selectedProductId);
                                            })
                                            ->where('created_at', '<=', $this->startDate->copy()->subSecond())
                                            ->sum('quantity');

            // Calculate final balance (sum of all movements up to endDate)
            $this->finalBalance = StockMovement::whereHas('productBatch', function($query) {
                                                $query->where('product_id', $this->selectedProductId);
                                            })
                                            ->where('created_at', '<=', $this->endDate)
                                            ->sum('quantity');
        } else {
            $this->initialBalance = 0;
            $this->finalBalance = 0;
        }
    }

    public function render()
    {
        $this->calculateBalances(); // Calculate balances before rendering

        $query = StockMovement::with(['productBatch.product'])
                                ->whereHas('productBatch', function($query) {
                                    $query->where('product_id', $this->selectedProductId);
                                })
                                ->whereBetween('created_at', [$this->startDate, $this->endDate]) // Filter movements within period
                                ->orderBy('created_at', 'desc');

        $stockMovements = $query->paginate(10);

        $years = range(Carbon::now()->year, Carbon::now()->year - 5); // Last 5 years
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create(null, $i, 1)->format('F');
        }

        return view('livewire.stock-card', compact('years', 'months', 'stockMovements'));
    }

    public function updatingSelectedProductId()
    {
        $this->resetPage();
        $this->calculateBalances(); // Recalculate balances when product changes
    }

    public function updatingMonth()
    {
        $this->resetPage();
        $this->updateDates(); // Update dates first
        $this->calculateBalances(); // Then recalculate balances
    }

    public function updatingYear()
    {
        $this->resetPage();
        $this->updateDates(); // Update dates first
        $this->calculateBalances(); // Then recalculate balances
    }
}

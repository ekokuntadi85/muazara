<?php

namespace App\Livewire;

use App\Exports\TopSellingProductsExport;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Title;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Laporan Produk Terlaris')]
class TopSellingProductsReport extends Component
{
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function filter()
    {
        // This method is called when the filter button is clicked.
        // The render method will be re-run with the new dates.
    }

    public function exportExcel()
    {
        return Excel::download(new TopSellingProductsExport($this->startDate, $this->endDate), 'laporan-produk-terlaris.xlsx');
    }

    public function exportPdf()
    {
        $url = route('reports.top-selling.print', ['startDate' => $this->startDate, 'endDate' => $this->endDate]);
        $this->dispatch('open-in-new-tab', url: $url);
    }

    public function render()
    {
        $topProducts = TransactionDetail::query()
            ->select(
                'product_id',
                'product_unit_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->whereHas('transaction', function ($query) {
                $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->with(['product:id,name', 'productUnit:id,name'])
            ->groupBy('product_id', 'product_unit_id')
            ->orderByDesc('total_quantity')
            ->limit(30)
            ->get();

        return view('livewire.top-selling-products-report', [
            'topProducts' => $topProducts,
        ]);
    }
}

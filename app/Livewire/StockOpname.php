<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Carbon\Carbon;

class StockOpname extends Component
{
    use WithPagination;

    public $selectedProductId; // Changed from selectedProductBatchId
    public $selectedProductName; // To display selected product name
    public $systemStock = 0;
    public $physicalStock;
    public $correctionRemarks;

    public $searchProduct = ''; // Changed from searchProductBatch
    public $productResults = []; // Changed from productBatchResults

    protected $rules = [
        'selectedProductId' => 'required|exists:products,id',
        'physicalStock' => 'required|integer|min:0',
        'correctionRemarks' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selectedProductId.required' => 'Produk wajib dipilih.',
        'selectedProductId.exists' => 'Produk tidak valid.',
        'physicalStock.required' => 'Stok fisik wajib diisi.',
        'physicalStock.integer' => 'Stok fisik harus berupa angka bulat.',
        'physicalStock.min' => 'Stok fisik tidak boleh negatif.',
    ];

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
        $this->calculateSystemStock();
    }

    public function calculateSystemStock()
    {
        if ($this->selectedProductId) {
            $product = Product::withSum('productBatches as total_stock', 'stock')->find($this->selectedProductId);
            $this->systemStock = $product->total_stock ?? 0;
        } else {
            $this->systemStock = 0;
        }
    }

    public function saveAdjustment()
    {
        $this->validate();

        DB::transaction(function () {
            $difference = $this->physicalStock - $this->systemStock;

            if ($difference !== 0) {
                $product = Product::find($this->selectedProductId);

                if ($difference < 0) { // Stock reduction
                    $remainingToDeduct = abs($difference);
                    $productBatches = $product->productBatches()->orderBy('expiration_date', 'asc')->get();

                    foreach ($productBatches as $batch) {
                        if ($remainingToDeduct <= 0) break;

                        $deductible = min($remainingToDeduct, $batch->stock);
                        
                        StockMovement::create([
                            'product_batch_id' => $batch->id,
                            'type' => 'OP',
                            'quantity' => -$deductible, // Negative for reduction
                            'remarks' => $this->correctionRemarks ?? 'Koreksi stok opname (pengurangan)',
                        ]);

                        $batch->stock -= $deductible;
                        $batch->save();
                        $remainingToDeduct -= $deductible;
                    }
                } else { // Stock addition
                    // Create a new batch for the added stock
                    $newBatch = ProductBatch::create([
                        'product_id' => $this->selectedProductId,
                        'batch_number' => 'OP-' . Carbon::now()->format('YmdHis'), // Unique batch number
                        'purchase_price' => 0, // Or some default/average price
                        'stock' => $difference,
                        'expiration_date' => Carbon::now()->addYears(10), // Far future expiry
                    ]);

                    StockMovement::create([
                        'product_batch_id' => $newBatch->id,
                        'type' => 'OP',
                        'quantity' => $difference, // Positive for addition
                        'remarks' => $this->correctionRemarks ?? 'Koreksi stok opname (penambahan)',
                    ]);
                }
            }
        });

        session()->flash('message', 'Koreksi stok berhasil disimpan.');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->selectedProductId = null;
        $this->selectedProductName = null;
        $this->systemStock = 0;
        $this->physicalStock = null;
        $this->correctionRemarks = null;
        $this->searchProduct = '';
        $this->productResults = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        $stockMovements = StockMovement::with(['productBatch.product'])
                                        ->where('type', 'OP')
                                        ->latest()
                                        ->paginate(10);

        return view('livewire.stock-opname', compact('stockMovements'));
    }
}

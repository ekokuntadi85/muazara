<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseEdit extends Component
{
    public $purchaseId;
    public $supplier_id;
    public $invoice_number;
    public $purchase_date;
    public $due_date;
    public $total_purchase_price;

    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $batch_number;
    public $purchase_price;
    public $stock;
    public $expiration_date;
    public $purchase_items = [];

    protected function rules()
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('purchases', 'invoice_number')->ignore($this->purchaseId),
            ],
            'purchase_date' => 'required|date',
            'due_date' => 'nullable|date',
            'total_purchase_price' => 'required|numeric|min:0',
            'purchase_items' => 'required|array|min:1',
            'purchase_items.*.product_id' => 'required|exists:products,id',
            'purchase_items.*.batch_number' => 'nullable|string|max:255',
            'purchase_items.*.purchase_price' => 'required|numeric|min:0',
            'purchase_items.*.stock' => 'required|integer|min:1',
            'purchase_items.*.expiration_date' => 'nullable|date',
        ];
    }

    protected $messages = [
        'supplier_id.required' => 'Supplier wajib dipilih.',
        'supplier_id.exists' => 'Supplier tidak valid.',
        'invoice_number.required' => 'Nomor invoice wajib diisi.',
        'invoice_number.unique' => 'Nomor invoice sudah ada.',
        'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
        'purchase_date.date' => 'Tanggal pembelian tidak valid.',
        'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
        'total_purchase_price.required' => 'Total pembelian wajib dihitung.',
        'total_purchase_price.numeric' => 'Total pembelian harus berupa angka.',
        'total_purchase_price.min' => 'Total pembelian tidak boleh negatif.',
        'purchase_items.required' => 'Setidaknya ada satu item pembelian.',
        'purchase_items.min' => 'Setidaknya ada satu item pembelian.',
        'product_id.required' => 'Produk wajib dipilih.',
        'product_id.exists' => 'Produk tidak valid.',
        'purchase_price.required' => 'Harga beli wajib diisi.',
        'purchase_price.numeric' => 'Harga beli harus berupa angka.',
        'purchase_price.min' => 'Harga beli tidak boleh negatif.',
        'stock.required' => 'Stok wajib diisi.',
        'stock.integer' => 'Stok harus berupa angka bulat.',
        'stock.min' => 'Stok minimal 1.',
        'expiration_date.date' => 'Tanggal kadaluarsa tidak valid.',
    ];

    public function mount(Purchase $purchase)
    {
        $this->purchaseId = $purchase->id;
        $this->supplier_id = $purchase->supplier_id;
        $this->invoice_number = $purchase->invoice_number;
        $this->purchase_date = $purchase->purchase_date;
        $this->due_date = $purchase->due_date;
        $this->total_purchase_price = $purchase->total_price;

        foreach ($purchase->productBatches as $batch) {
            $this->purchase_items[] = [
                'id' => $batch->id, // Keep batch ID for update/delete
                'product_id' => $batch->product_id,
                'product_name' => $batch->product->name,
                'batch_number' => $batch->batch_number,
                'purchase_price' => $batch->purchase_price,
                'stock' => $batch->stock,
                'expiration_date' => $batch->expiration_date,
                'subtotal' => $batch->purchase_price * $batch->stock,
            ];
        }
    }

    public function updatedPurchaseDate($value)
    {
        if ($value) {
            $this->due_date = \Illuminate\Support\Carbon::parse($value)->addDays(30)->format('Y-m-d');
        } else {
            $this->due_date = null;
        }
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($this->searchProduct) >= 1) {
            $this->searchResults = Product::where('name', 'like', '%' . $value . '%')
                ->orWhere('sku', 'like', '%' . $value . '%')
                ->limit(5)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        $this->product_id = $product->id;
        $this->selectedProductName = $product->name;
        
        // Get the last purchase price for the selected product
        $lastPurchase = ProductBatch::where('product_id', $productId)->latest()->first();
        $this->purchase_price = $lastPurchase ? $lastPurchase->purchase_price : 0;

        $this->searchProduct = '';
        $this->searchResults = [];
    }

    public function addItem()
    {
        $this->validate([
            'product_id' => 'required|exists:products,id',
            'purchase_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'batch_number' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
        ]);

        $product = Product::find($this->product_id);

        $this->purchase_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'batch_number' => $this->batch_number,
            'purchase_price' => $this->purchase_price,
            'stock' => $this->stock,
            'expiration_date' => $this->expiration_date,
            'subtotal' => $this->purchase_price * $this->stock,
        ];

        $this->calculateTotalPurchasePrice();
        $this->resetItemForm();
    }

    public function removeItem($index)
    {
        unset($this->purchase_items[$index]);
        $this->purchase_items = array_values($this->purchase_items);
        $this->calculateTotalPurchasePrice();
    }

    private function calculateTotalPurchasePrice()
    {
        $this->total_purchase_price = array_sum(array_column($this->purchase_items, 'subtotal'));
    }

    public function savePurchase()
    {
        $this->validate();

        DB::transaction(function () {
            $purchase = Purchase::findOrFail($this->purchaseId);
            $purchase->update([
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'due_date' => $this->due_date,
                'total_price' => $this->total_purchase_price,
                'supplier_id' => $this->supplier_id,
            ]);

            $existingBatchIds = $purchase->productBatches->pluck('id')->toArray();
            $updatedBatchIds = [];

            foreach ($this->purchase_items as $item) {
                if (isset($item['id'])) {
                    $batch = ProductBatch::find($item['id']);
                    if ($batch) {
                        $batch->update([
                            'product_id' => $item['product_id'],
                            'batch_number' => $item['batch_number'],
                            'purchase_price' => $item['purchase_price'],
                            'stock' => $item['stock'],
                            'expiration_date' => $item['expiration_date'],
                        ]);
                        $updatedBatchIds[] = $batch->id;
                    }
                } else {
                    $batch = ProductBatch::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'batch_number' => $item['batch_number'],
                        'purchase_price' => $item['purchase_price'],
                        'stock' => $item['stock'],
                        'expiration_date' => $item['expiration_date'],
                    ]);
                    $updatedBatchIds[] = $batch->id;
                }
            }

            ProductBatch::where('purchase_id', $purchase->id)
                        ->whereNotIn('id', $updatedBatchIds)
                        ->delete();
        });

        session()->flash('message', 'Pembelian berhasil diperbarui.');
        return redirect()->route('purchases.index');
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->selectedProductName = '';
        $this->batch_number = '';
        $this->purchase_price = '';
        $this->stock = '';
        $this->expiration_date = '';
        $this->resetErrorBag(['product_id', 'batch_number', 'purchase_price', 'stock', 'expiration_date']);
    }

    public function render()
    {
        $suppliers = Supplier::all();
        return view('livewire.purchase-edit', compact('suppliers'));
    }
}
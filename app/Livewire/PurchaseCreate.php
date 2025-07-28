<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseCreate extends Component
{
    public $supplier_id;
    public $invoice_number;
    public $purchase_date;
    public $due_date;
    public $total_purchase_price = 0;
    public $payment_status = 'unpaid';

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

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
        'purchase_date' => 'required|date',
        'due_date' => 'nullable|date',
        'total_purchase_price' => 'required|numeric|min:0',
        'purchase_items' => 'required|array|min:1',
        'purchase_items.*.product_id' => 'required|exists:products,id',
        'purchase_items.*.batch_number' => 'nullable|string|max:255',
        'purchase_items.*.purchase_price' => 'required|numeric|min:0',
        'purchase_items.*.stock' => 'required|integer|min:1',
        'purchase_items.*.expiration_date' => 'required|date',
    ];

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
        'batch_number' => 'nullable|string|max:255',
        'purchase_price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:1',
        'expiration_date' => 'required|date',
    ];

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

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
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
        if (empty($value)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $value . '%')
                                    ->orWhere('sku', 'like', '%' . $value . '%')
                                    ->limit(10)
                                    ->get();
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results
        }
    }

    public function addItem()
    {
        $this->validate($this->itemRules);

        $product = Product::find($this->product_id);

        $this->purchase_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name, // Store product name for display
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
        $this->purchase_items = array_values($this->purchase_items); // Re-index the array
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
            $purchase = Purchase::create([
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'due_date' => $this->due_date,
                'total_price' => $this->total_purchase_price,
                'supplier_id' => $this->supplier_id,
                'payment_status' => $this->payment_status,
            ]);

            foreach ($this->purchase_items as $item) {
                $batchNumber = empty($item['batch_number']) ? '-' : $item['batch_number'];
                ProductBatch::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'batch_number' => $batchNumber,
                    'purchase_price' => $item['purchase_price'],
                    'stock' => $item['stock'],
                    'expiration_date' => $item['expiration_date'],
                ]);
            }
        });

        session()->flash('message', 'Pembelian berhasil dicatat.');
        $this->resetAll();
        return redirect()->route('purchases.index');
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->batch_number = '';
        $this->purchase_price = '';
        $this->stock = '';
        $this->expiration_date = '';
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductName = '';
        $this->resetErrorBag(['product_id', 'batch_number', 'purchase_price', 'stock', 'expiration_date']);
    }

    private function resetAll()
    {
        $this->supplier_id = '';
        $this->invoice_number = '';
        $this->purchase_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->total_purchase_price = 0;
        $this->purchase_items = [];
        $this->payment_status = 'unpaid';
        $this->resetItemForm();
        $this->resetErrorBag();
    }

    public function render()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('livewire.purchase-create', compact('suppliers', 'products'));
    }
}
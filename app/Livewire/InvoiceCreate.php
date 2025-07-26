<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class InvoiceCreate extends Component
{
    public $customer_id;
    public $due_date;
    public $total_price = 0;

    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $quantity;
    public $price; // Price will be product's selling_price
    public $invoice_items = [];

    public $currentDateTime;
    public $loggedInUser;

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'due_date' => 'required|date|after_or_equal:today',
        'invoice_items' => 'required|array|min:1',
        'invoice_items.*.product_id' => 'required|exists:products,id',
        'invoice_items.*.quantity' => 'required|integer|min:1',
        'invoice_items.*.price' => 'required|numeric|min:0',
    ];

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ];

    protected $messages = [
        'customer_id.required' => 'Customer wajib dipilih.',
        'customer_id.exists' => 'Customer tidak valid.',
        'due_date.required' => 'Tanggal jatuh tempo wajib diisi.',
        'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
        'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh kurang dari hari ini.',
        'invoice_items.required' => 'Setidaknya ada satu item invoice.',
        'invoice_items.min' => 'Setidaknya ada satu item invoice.',
        'product_id.required' => 'Produk wajib dipilih.',
        'product_id.exists' => 'Produk tidak valid.',
        'quantity.required' => 'Kuantitas wajib diisi.',
        'quantity.integer' => 'Kuantitas harus berupa angka bulat.',
        'quantity.min' => 'Kuantitas minimal 1.',
    ];

    public function mount()
    {
        // Set default due date to 30 days from now
        $this->due_date = Carbon::now()->addDays(30)->format('Y-m-d');
        $this->updateDateTimeAndUser();
    }

    private function updateDateTimeAndUser()
    {
        $this->currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $this->loggedInUser = Auth::check() ? Auth::user()->name : 'Guest';
    }

    public function render()
    {
        $customers = Customer::all();
        return view('livewire.invoice-create', compact('customers'));
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
            $this->price = $product->selling_price; // Auto-fill price
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results
        }
    }

    public function addItem()
    {
        $this->validate($this->itemRules);

        $product = Product::find($this->product_id);

        // Check stock before adding item
        if ($product->total_stock < $this->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product->total_stock,
            ]);
        }

        $this->invoice_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'quantity' => $this->quantity,
            'price' => $product->selling_price, // Use selling price for invoice
            'subtotal' => $this->quantity * $product->selling_price,
        ];

        $this->calculateTotalPrice();
        $this->resetItemForm();
    }

    public function removeItem($index)
    {
        unset($this->invoice_items[$index]);
        $this->invoice_items = array_values($this->invoice_items); // Re-index the array
        $this->calculateTotalPrice();
    }

    private function calculateTotalPrice()
    {
        $this->total_price = array_sum(array_column($this->invoice_items, 'subtotal'));
    }

    public function saveInvoice()
    {
        $this->validate();

        DB::transaction(function () {
            $transaction = Transaction::create([
                'type' => 'invoice',
                'payment_status' => 'unpaid',
                'total_price' => $this->total_price,
                'due_date' => $this->due_date,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(), // Assign current logged in user
                'invoice_number' => 'INV-' . Carbon::now()->format('YmdHis') . '-' . uniqid(), // Auto-generate invoice number
            ]);

            foreach ($this->invoice_items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Reduce stock from product batches (FEFO)
                $product = Product::find($item['product_id']);
                $remainingQuantity = $item['quantity'];

                foreach ($product->productBatches()->orderBy('expiration_date', 'asc')->get() as $batch) {
                    if ($remainingQuantity <= 0) break;

                    $deductible = min($remainingQuantity, $batch->stock);
                    $batch->stock -= $deductible;
                    $batch->save();
                    $remainingQuantity -= $deductible;
                }
            }
        });

        session()->flash('message', 'Invoice berhasil dibuat.');
        $this->resetAll();
        return redirect()->route('transactions.index');
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->quantity = '';
        $this->price = '';
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductName = '';
        $this->resetErrorBag(['product_id', 'quantity']);
    }

    private function resetAll()
    {
        $this->customer_id = '';
        $this->due_date = Carbon::now()->addDays(30)->format('Y-m-d');
        $this->total_price = 0;
        $this->invoice_items = [];
        $this->resetItemForm();
        $this->resetErrorBag();
        $this->updateDateTimeAndUser(); // Update date/time and user
    }
}

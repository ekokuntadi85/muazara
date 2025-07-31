<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Livewire\WithPagination;

class InvoiceCreate extends Component
{
    use WithPagination;

    public $customer_id;
    public $due_date;
    public $total_price = 0;
    public $invoice_items = [];

    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $quantity;
    public $price;

    // Default values for invoice
    public $type = 'invoice';
    public $payment_status = 'unpaid';
    public $invoiceNumber; // Make sure this is public

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'due_date' => 'required|date',
        'invoice_items' => 'required|array|min:1',
        'invoice_items.*.product_id' => 'required|exists:products,id',
        'invoice_items.*.quantity' => 'required|integer|min:1',
        'invoice_items.*.price' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'customer_id.required' => 'Pelanggan wajib dipilih.',
        'due_date.required' => 'Tanggal jatuh tempo wajib diisi.',
        'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
        'invoice_items.required' => 'Setidaknya ada satu item invoice.',
        'invoice_items.min' => 'Setidaknya ada satu item invoice.',
        'invoice_items.*.product_id.required' => 'Produk wajib dipilih.',
        'invoice_items.*.product_id.exists' => 'Produk tidak valid.',
        'invoice_items.*.quantity.required' => 'Kuantitas wajib diisi.',
        'invoice_items.*.quantity.integer' => 'Kuantitas harus berupa angka bulat.',
        'invoice_items.*.quantity.min' => 'Kuantitas minimal 1.',
        'invoice_items.*.price.required' => 'Harga satuan wajib diisi.',
        'invoice_items.*.price.numeric' => 'Harga satuan harus berupa angka.',
        'invoice_items.*.price.min' => 'Harga satuan tidak boleh negatif.',
    ];

    public function mount()
    {
        // Default values are already set as public properties
        // $this->type = 'invoice';
        // $this->payment_status = 'unpaid';
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($this->searchProduct) >= 1) {
            $this->searchResults = Product::where('name', 'like', '%' . $value . '%')
                                    ->orWhere('sku', 'like', '%' . $value . '%')
                                    ->limit(10)
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
        $this->price = $product->selling_price; // Set price to selling price
        $this->searchProduct = '';
        $this->searchResults = [];
    }

    public function addItem()
    {
        $this->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Product::find($this->product_id);

        if ($product->total_stock < $this->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product->total_stock,
            ]);
        }

        $this->invoice_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->quantity * $this->price,
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

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->quantity = '';
        $this->price = '';
        $this->selectedProductName = '';
        $this->resetErrorBag(['product_id', 'quantity', 'price']);
    }

    public function saveInvoice()
    {
        $this->validate();

        DB::transaction(function () use (&$transaction) {
            $this->invoiceNumber = 'INV-' . Carbon::now()->format('YmdHis');

            $transaction = Transaction::create([
                'type' => $this->type,
                'payment_status' => $this->payment_status,
                'total_price' => $this->total_price,
                'amount_paid' => 0, // For invoice, amount paid is 0 initially
                'change' => 0,
                'due_date' => $this->due_date,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
                'invoice_number' => $this->invoiceNumber, // Use $this->invoiceNumber
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

        session()->flash('message', 'Invoice berhasil dicatat dengan No. Invoice: ' . $this->invoiceNumber);
        return redirect()->route('transactions.index');
    }

    public function render()
    {
        $customers = Customer::all();
        return view('livewire.invoice-create', compact('customers'));
    }
}

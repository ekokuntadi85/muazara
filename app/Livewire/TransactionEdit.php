<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransactionEdit extends Component
{
    public $transactionId;
    public $type;
    public $payment_status;
    public $total_price;
    public $due_date;
    public $customer_id;

    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $quantity;
    public $price;
    public $transaction_items = [];
    public $original_transaction_items = []; // To store original quantities for stock adjustment

    protected function rules()
    {
        return [
            'type' => 'required|in:pos,invoice',
            'payment_status' => 'required|in:paid,unpaid',
            'total_price' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
            'transaction_items' => 'required|array|min:1',
            'transaction_items.*.product_id' => 'required|exists:products,id',
            'transaction_items.*.quantity' => 'required|integer|min:1',
            'transaction_items.*.price' => 'required|numeric|min:0',
        ];
    }

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'type.required' => 'Tipe transaksi wajib diisi.',
        'type.in' => 'Tipe transaksi tidak valid.',
        'payment_status.required' => 'Status pembayaran wajib diisi.',
        'payment_status.in' => 'Status pembayaran tidak valid.',
        'total_price.required' => 'Total harga wajib diisi.',
        'total_price.numeric' => 'Total harga harus berupa angka.',
        'total_price.min' => 'Total harga tidak boleh negatif.',
        'transaction_items.required' => 'Setidaknya ada satu item transaksi.',
        'transaction_items.min' => 'Setidaknya ada satu item transaksi.',
        'product_id.required' => 'Produk wajib dipilih.',
        'product_id.exists' => 'Produk tidak valid.',
        'quantity.required' => 'Kuantitas wajib diisi.',
        'quantity.integer' => 'Kuantitas harus berupa angka bulat.',
        'quantity.min' => 'Kuantitas minimal 1.',
        'price.required' => 'Harga wajib diisi.',
        'price.numeric' => 'Harga harus berupa angka.',
        'price.min' => 'Harga tidak boleh negatif.',
    ];

    public function mount(Transaction $transaction)
    {
        $this->transactionId = $transaction->id;
        $this->type = $transaction->type;
        $this->payment_status = $transaction->payment_status;
        $this->total_price = $transaction->total_price;
        $this->due_date = $transaction->due_date;
        $this->customer_id = $transaction->customer_id;

        foreach ($transaction->transactionDetails as $detail) {
            $item = [
                'id' => $detail->id, // Keep detail ID for update/delete
                'product_id' => $detail->product_id,
                'product_name' => $detail->product->name,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
                'subtotal' => $detail->quantity * $detail->price,
            ];
            $this->transaction_items[] = $item;
            $this->original_transaction_items[] = $item; // Store original for stock adjustment
        }
    }

    public function render()
    {
        $customers = Customer::all();
        // Products are fetched dynamically via search
        return view('livewire.transaction-edit', compact('customers'));
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
        // Need to consider existing quantity if updating an item
        $currentQuantityInForm = 0;
        foreach ($this->transaction_items as $item) {
            if ($item['product_id'] == $this->product_id) {
                $currentQuantityInForm += $item['quantity'];
            }
        }

        $availableStock = $product->total_stock - $currentQuantityInForm;

        if ($availableStock < $this->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $availableStock,
            ]);
        }

        $this->transaction_items[] = [
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
        unset($this->transaction_items[$index]);
        $this->transaction_items = array_values($this->transaction_items); // Re-index the array
        $this->calculateTotalPrice();
    }

    private function calculateTotalPrice()
    {
        $this->total_price = array_sum(array_column($this->transaction_items, 'subtotal'));
    }

    public function saveTransaction()
    {
        $this->validate();

        DB::transaction(function () {
            $transaction = Transaction::findOrFail($this->transactionId);

            // Revert stock for original items
            foreach ($this->original_transaction_items as $originalItem) {
                $product = Product::find($originalItem['product_id']);
                if ($product) {
                    // Find a batch to add stock back to (simplistic: add to any batch)
                    $batch = $product->productBatches()->first();
                    if ($batch) {
                        $batch->stock += $originalItem['quantity'];
                        $batch->save();
                    }
                }
            }

            $transaction->update([
                'type' => $this->type,
                'payment_status' => $this->payment_status,
                'total_price' => $this->total_price,
                'due_date' => $this->due_date,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(), // Assign current logged in user
            ]);

            // Sync transaction details and deduct new stock
            $existingDetailIds = $transaction->transactionDetails->pluck('id')->toArray();
            $updatedDetailIds = [];

            foreach ($this->transaction_items as $item) {
                if (isset($item['id'])) {
                    // Update existing detail
                    $detail = TransactionDetail::find($item['id']);
                    if ($detail) {
                        $detail->update([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                        ]);
                        $updatedDetailIds[] = $detail->id;
                    }
                } else {
                    // Create new detail
                    $detail = TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                    $updatedDetailIds[] = $detail->id;
                }

                // Deduct stock from product batches for new/updated items
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

            // Delete details that are no longer in the list
            TransactionDetail::where('transaction_id', $transaction->id)
                             ->whereNotIn('id', $updatedDetailIds)
                             ->delete();
        });

        session()->flash('message', 'Transaksi berhasil diperbarui.');
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
        $this->resetErrorBag(['product_id', 'quantity', 'price']);
    }
}
<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailBatch;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;

#[Title('Edit Transaksi')]
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
    public $product_units = [];
    public $product_unit_id;
    public $quantity;
    public $price;
    public $stock_warning = '';
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
        'product_unit_id' => 'required|exists:product_units,id',
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
        $product = Product::with('productUnits')->find($productId);
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            $this->product_units = $product->productUnits;
            $this->product_unit_id = null; // Reset unit selection
            $this->price = null; // Reset price
            $this->quantity = ''; // Reset quantity
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results
        }
    }

    private function checkStockAvailability()
    {
        $this->stock_warning = '';
        if (!$this->product_id || !$this->product_unit_id || !$this->quantity || $this->quantity <= 0) {
            return;
        }

        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->product_units)->firstWhere('id', $this->product_unit_id);

        if (!$product || !$selectedUnit) {
            return;
        }

        $requestedStockInBaseUnit = $this->quantity * $selectedUnit['conversion_factor'];

        if ($product->total_stock < $requestedStockInBaseUnit) {
            $this->stock_warning = 'Stok tidak mencukupi. Stok tersedia: ' . floor($product->total_stock / $selectedUnit['conversion_factor']) . ' ' . $selectedUnit['name'] . '.';
        }
    }

    public function updatedProductUnitId($unitId)
    {
        if ($unitId) {
            $selectedUnit = collect($this->product_units)->firstWhere('id', $unitId);
            if ($selectedUnit) {
                $this->price = $selectedUnit['selling_price'];
            }
        }
        $this->checkStockAvailability();
    }

    public function updatedQuantity()
    {
        $this->checkStockAvailability();
    }

    public function addItem()
    {
        $this->checkStockAvailability();
        if (!empty($this->stock_warning)) {
            return;
        }

        $this->validate($this->itemRules);

        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->product_units)->firstWhere('id', $this->product_unit_id);

        $items = $this->transaction_items;
        $items[] = [
            'product_id' => $this->product_id,
            'product_unit_id' => $this->product_unit_id,
            'product_name' => $product->name . ' (' . $selectedUnit['name'] . ')',
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->quantity * $this->price,
        ];
        $this->transaction_items = $items;

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
            $transaction = Transaction::with('transactionDetails.transactionDetailBatches.productBatch')->findOrFail($this->transactionId);

            // Revert stock based on transactionDetailBatches
            foreach ($transaction->transactionDetails as $detail) {
                foreach ($detail->transactionDetailBatches as $detailBatch) {
                    if ($detailBatch->productBatch) {
                        $detailBatch->productBatch->increment('stock', $detailBatch->quantity);
                    }
                }
            }

            // Delete old transaction detail batches
            $transaction->transactionDetails()->each(function ($detail) {
                $detail->transactionDetailBatches()->delete();
            });


            $transaction->update([
                'type' => $this->type,
                'payment_status' => $this->payment_status,
                'total_price' => $this->total_price,
                'due_date' => $this->due_date,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
            ]);

            $currentDetailIds = collect($this->transaction_items)->pluck('id')->filter();
            $transaction->transactionDetails()->whereNotIn('id', $currentDetailIds)->delete();

            foreach ($this->transaction_items as $item) {
                $detail = $transaction->transactionDetails()->updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]
                );

                $product = Product::find($item['product_id']);
                $remainingQuantity = $item['quantity'];

                $batches = $product->productBatches()->where('stock', '>', 0)->orderBy('expiration_date', 'asc')->get();

                foreach ($batches as $batch) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    $quantityToDeduct = min($remainingQuantity, $batch->stock);

                    $batch->decrement('stock', $quantityToDeduct);

                    TransactionDetailBatch::create([
                        'transaction_detail_id' => $detail->id,
                        'product_batch_id' => $batch->id,
                        'quantity' => $quantityToDeduct,
                    ]);

                    $remainingQuantity -= $quantityToDeduct;
                }

                if ($remainingQuantity > 0) {
                    throw ValidationException::withMessages(['quantity' => 'Stok produk tidak mencukupi.']);
                }
            }
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

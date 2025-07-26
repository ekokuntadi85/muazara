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

class PointOfSale extends Component
{
    public $cart_items = [];
    public $customer_id;
    public $total_price = 0;

    public $search = '';
    public $searchResults = [];
    public $highlightedIndex = 0;

    public $amount_paid;
    public $change = 0;

    protected $rules = [
        'customer_id' => 'nullable|exists:customers,id',
        'cart_items' => 'required|array|min:1',
        'amount_paid' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'cart_items.required' => 'Keranjang belanja tidak boleh kosong.',
        'cart_items.min' => 'Keranjang belanja tidak boleh kosong.',
        'amount_paid.required' => 'Jumlah bayar wajib diisi.',
        'amount_paid.numeric' => 'Jumlah bayar harus berupa angka.',
        'amount_paid.min' => 'Jumlah bayar tidak boleh negatif.',
    ];

    public function mount()
    {
        // Set default customer to 'UMUM'
        $umumCustomer = Customer::firstOrCreate(
            ['name' => 'UMUM'],
            ['phone' => null, 'address' => null]
        );
        $this->customer_id = $umumCustomer->id;
    }

    public function updatedSearch($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            $this->highlightedIndex = 0;
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $value . '%')
                                    ->orWhere('sku', 'like', '%' . $value . '%')
                                    ->limit(10)
                                    ->get();
        $this->highlightedIndex = 0;
    }

    public function incrementHighlight()
    {
        if ($this->highlightedIndex === count($this->searchResults) - 1) {
            $this->highlightedIndex = 0;
            return;
        }
        $this->highlightedIndex++;
    }

    public function decrementHighlight()
    {
        if ($this->highlightedIndex === 0) {
            $this->highlightedIndex = count($this->searchResults) - 1;
            return;
        }
        $this->highlightedIndex--;
    }

    public function selectHighlightedProduct()
    {
        if (!empty($this->searchResults) && isset($this->searchResults[$this->highlightedIndex])) {
            $productId = $this->searchResults[$this->highlightedIndex]->id;
            $this->addProduct($productId);
        }
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        // Check if product already in cart
        $found = false;
        foreach ($this->cart_items as $index => $item) {
            if ($item['product_id'] == $productId) {
                $newQuantity = $item['quantity'] + 1;
                if ($product->total_stock < $newQuantity) {
                    throw ValidationException::withMessages([
                        'search' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product->total_stock,
                    ]);
                }
                $this->cart_items[$index]['quantity'] = $newQuantity;
                $this->cart_items[$index]['subtotal'] = $newQuantity * $item['price'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            if ($product->total_stock < 1) {
                throw ValidationException::withMessages([
                    'search' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product->total_stock,
                ]);
            }
            $this->cart_items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'price' => $product->selling_price,
                'subtotal' => $product->selling_price,
            ];
        }

        $this->calculateTotalPrice();
        $this->search = '';
        $this->searchResults = [];
        $this->highlightedIndex = 0;
    }

    public function removeItem($index)
    {
        unset($this->cart_items[$index]);
        $this->cart_items = array_values($this->cart_items); // Re-index the array
        $this->calculateTotalPrice();
    }

    public function updateQuantity($index, $quantity)
    {
        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }

        $item = $this->cart_items[$index];
        $product = Product::find($item['product_id']);

        if ($product->total_stock < $quantity) {
            throw ValidationException::withMessages([
                'cart_items.' . $index . '.quantity' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product->total_stock,
            ]);
        }

        $this->cart_items[$index]['quantity'] = $quantity;
        $this->cart_items[$index]['subtotal'] = $quantity * $item['price'];
        $this->calculateTotalPrice();
    }

    private function calculateTotalPrice()
    {
        $this->total_price = array_sum(array_column($this->cart_items, 'subtotal'));
        $this->calculateChange();
    }

    public function updatedAmountPaid()
    {
        $this->calculateChange();
    }

    private function calculateChange()
    {
        $this->change = $this->amount_paid - $this->total_price;
    }

    public function checkout()
    {
        $this->validate();

        if ($this->amount_paid < $this->total_price) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Jumlah bayar tidak mencukupi.',
            ]);
        }

        DB::transaction(function () {
            $transaction = Transaction::create([
                'type' => 'pos',
                'payment_status' => 'paid',
                'total_price' => $this->total_price,
                'due_date' => null, // POS transactions usually don't have due date
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
            ]);

            foreach ($this->cart_items as $item) {
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

        session()->flash('message', 'Transaksi POS berhasil dicatat.');
        $this->resetAll();
        $this->dispatch('focusSearchInput'); // Dispatch event to focus search input
    }

    private function resetAll()
    {
        $this->cart_items = [];
        $this->total_price = 0;
        $this->search = '';
        $this->searchResults = [];
        $this->amount_paid = null;
        $this->change = 0;
        $this->highlightedIndex = 0;
        // Reset customer to UMUM
        $umumCustomer = Customer::firstOrCreate(
            ['name' => 'UMUM'],
            ['phone' => null, 'address' => null]
        );
        $this->customer_id = $umumCustomer->id;
        $this->resetErrorBag();
    }

    public function render()
    {
        $customers = Customer::all();
        return view('livewire.point-of-sale', compact('customers'));
    }
}

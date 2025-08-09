<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Point of Sale')]
class PointOfSale extends Component
{
    use WithPagination;

    public $cart_items = [];
    public $customer_id;
    public $total_price = 0;
    public $search = '';
    public $amount_paid;
    public $change = 0;
    public $currentDateTime;
    public $loggedInUser;
    public $invoiceNumber;

    // Modal state
    public $isUnitModalVisible = false;
    public $productForModal = null;
    public $unitsForModal = [];
    public $quantityToAdd = 1;

    protected $rules = [
        'customer_id' => 'nullable|exists:customers,id',
        'cart_items' => 'required|array|min:1',
        'amount_paid' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'cart_items.required' => 'Keranjang belanja tidak boleh kosong.',
        'amount_paid.required' => 'Jumlah bayar wajib diisi.',
    ];

    public function mount()
    {
        $umumCustomer = Customer::firstOrCreate(['name' => 'UMUM'], ['phone' => null, 'address' => null]);
        $this->customer_id = $umumCustomer->id;
        $this->updateDateTimeAndUser();
        $this->dispatch('focus-search-input');
    }

    private function updateDateTimeAndUser()
    {
        $this->currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $this->loggedInUser = Auth::check() ? Auth::user()->name : 'Guest';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function searchProducts()
    {
        // This is handled by render() method with wire:model.live
    }

    public function selectProduct($productId)
    {
        $this->productForModal = Product::with('productUnits', 'productBatches')->find($productId);
        if (!$this->productForModal) return;

        $this->unitsForModal = $this->productForModal->productUnits->map(function ($unit) {
            $totalStockInBase = $this->productForModal->productBatches->sum('stock');
            $unit->stock_in_unit = ($unit->conversion_factor > 0) ? floor($totalStockInBase / $unit->conversion_factor) : 0;
            return $unit;
        });
        $this->quantityToAdd = 1;
        $this->isUnitModalVisible = true;
        $this->resetErrorBag();
    }

    public function addItemToCart($unitId)
    {
        $this->validate(['quantityToAdd' => 'required|integer|min:1']);

        $product = $this->productForModal;
        $selectedUnit = collect($this->unitsForModal)->firstWhere('id', $unitId);

        if (!$product || !$selectedUnit) {
            $this->closeUnitModal();
            return;
        }

        $quantityInBaseUnits = $this->quantityToAdd * $selectedUnit->conversion_factor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $quantityInBaseUnits) {
            $this->addError('quantityToAdd', 'Stok tidak mencukupi.');
            return;
        }

        $foundIndex = -1;
        foreach ($this->cart_items as $index => $item) {
            if ($item['product_id'] == $product->id && $item['product_unit_id'] == $selectedUnit->id) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== -1) {
            $this->cart_items[$foundIndex]['original_quantity_input'] += $this->quantityToAdd;
            $this->cart_items[$foundIndex]['quantity'] += $quantityInBaseUnits;
            $this->cart_items[$foundIndex]['subtotal'] = $this->cart_items[$foundIndex]['original_quantity_input'] * $this->cart_items[$foundIndex]['price'];
        } else {
            $this->cart_items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_unit_id' => $selectedUnit->id,
                'unit_name' => $selectedUnit->name,
                'conversion_factor' => $selectedUnit->conversion_factor,
                'original_quantity_input' => $this->quantityToAdd,
                'quantity' => $quantityInBaseUnits,
                'price' => $selectedUnit->selling_price,
                'subtotal' => $this->quantityToAdd * $selectedUnit->selling_price,
            ];
        }

        $this->calculateTotalPrice();
        $this->closeUnitModal();
        $this->search = '';
        $this->dispatch('focus-search-input');
    }

    public function closeUnitModal()
    {
        $this->isUnitModalVisible = false;
        $this->productForModal = null;
        $this->unitsForModal = [];
        $this->quantityToAdd = 1;
        $this->resetErrorBag();
    }

    public function removeItem($index)
    {
        unset($this->cart_items[$index]);
        $this->cart_items = array_values($this->cart_items);
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
        $selectedUnit = ProductUnit::find($item['product_unit_id']);

        if (!$product || !$selectedUnit) return;

        $newQuantityBase = $quantity * $selectedUnit->conversion_factor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $newQuantityBase) {
            session()->flash('error', 'Stok tidak cukup untuk ' . $product->name);
            return;
        }

        $this->cart_items[$index]['original_quantity_input'] = $quantity;
        $this->cart_items[$index]['quantity'] = $newQuantityBase;
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
            $this->addError('amount_paid', 'Jumlah bayar tidak mencukupi.');
            return;
        }

        $stockIsSufficient = true;
        $insufficientItemName = '';
        $transaction = null;

        DB::transaction(function () use (&$transaction, &$stockIsSufficient, &$insufficientItemName) {
            foreach ($this->cart_items as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                $totalStockInBaseUnits = $product->productBatches()->sum('stock');

                if ($totalStockInBaseUnits < $item['quantity']) {
                    $stockIsSufficient = false;
                    $insufficientItemName = $item['product_name'];
                    return; // Rollback transaction
                }
            }

            $this->invoiceNumber = 'POS-' . Carbon::now()->format('YmdHis');

            $transaction = Transaction::create([
                'type' => 'POS',
                'payment_status' => 'paid',
                'total_price' => $this->total_price,
                'amount_paid' => $this->amount_paid,
                'change' => $this->change,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
                'invoice_number' => $this->invoiceNumber,
            ]);

            foreach ($this->cart_items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                
            }
        });

        if (!$stockIsSufficient) {
            $this->addError('cart_items', 'Stok untuk ' . $insufficientItemName . ' tidak lagi mencukupi.');
            return;
        }

        if ($transaction) {
            session()->flash('message', 'Transaksi POS berhasil dicatat dengan No. Nota: ' . $this->invoiceNumber);
            $this->dispatch('transaction-completed', ['transactionId' => $transaction->id]);
            $this->resetAll();
        }
    }

    private function resetAll()
    {
        $this->cart_items = [];
        $this->total_price = 0;
        $this->search = '';
        $this->amount_paid = null;
        $this->change = 0;
        $this->invoiceNumber = null;
        $this->updateDateTimeAndUser();
        $umumCustomer = Customer::firstOrCreate(['name' => 'UMUM']);
        $this->customer_id = $umumCustomer->id;
        $this->resetErrorBag();
    }

    public function render()
    {
        $customers = Customer::all();
        $products = Product::query();

        if (!empty($this->search)) {
            $products->where('name', 'like', '%' . $this->search . '%')
                     ->orWhere('sku', 'like', '%' . $this->search . '%');
        }

        $products = $products->with(['productUnits', 'productBatches'])->paginate(10);

        return view('livewire.point-of-sale', compact('customers', 'products'));
    }
}

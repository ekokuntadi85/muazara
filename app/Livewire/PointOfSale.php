<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\ProductUnit; // Import ProductUnit
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Livewire\WithPagination;

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

    // New state for the unit selection modal
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

        $this->updateDateTimeAndUser();
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
        if (empty($this->search)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                                    ->limit(10)
                                    ->get();
    }

    public function selectProduct($productId)
    {
        $this->productForModal = Product::with('productUnits', 'productBatches')->find($productId);
        $this->unitsForModal = $this->productForModal->productUnits->map(function ($unit) {
            $totalStockInBase = $this->productForModal->productBatches->sum('stock');
            $unit->stock_in_unit = ($unit->conversion_factor > 0) ? floor($totalStockInBase / $unit->conversion_factor) : 0;
            return $unit;
        });
        $this->quantityToAdd = 1;
        $this->isUnitModalVisible = true;
    }

    public function addItemToCart($unitId)
    {
        $this->validate(['quantityToAdd' => 'required|integer|min:1']);

        $product = $this->productForModal;
        $selectedUnit = collect($this->unitsForModal)->firstWhere('id', $unitId);

        if (!$product || !$selectedUnit) {
            session()->flash('error', 'Produk atau satuan tidak valid.');
            $this->closeUnitModal();
            return;
        }

        $quantityInBaseUnits = $this->quantityToAdd * $selectedUnit->conversion_factor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $quantityInBaseUnits) {
            $this->addError('quantityToAdd', 'Stok tidak cukup. Tersedia: ' . $selectedUnit->stock_in_unit . ' ' . $selectedUnit->name);
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
            // Update quantity if found
            $newQuantity = $this->cart_items[$foundIndex]['original_quantity_input'] + $this->quantityToAdd;
            $newQuantityInBase = $newQuantity * $this->cart_items[$foundIndex]['conversion_factor'];

            // Re-validate stock for the new total quantity
            $totalStockInBaseUnits = $product->productBatches->sum('stock');
            if ($totalStockInBaseUnits < $newQuantityInBase) {
                $this->addError('quantityToAdd', 'Stok tidak cukup untuk menambahkan. Tersedia: ' . $selectedUnit->stock_in_unit . ' ' . $selectedUnit->name);
                return;
            }

            $this->cart_items[$foundIndex]['original_quantity_input'] = $newQuantity;
            $this->cart_items[$foundIndex]['quantity'] = $newQuantityInBase;
            $this->cart_items[$foundIndex]['subtotal'] = $newQuantity * $this->cart_items[$foundIndex]['price'];
        } else {
            // Add as a new item if not found
            $this->cart_items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_unit_id' => $selectedUnit->id,
                'unit_name' => $selectedUnit->name,
                'conversion_factor' => $selectedUnit->conversion_factor,
                'original_quantity_input' => $this->quantityToAdd,
                'quantity' => $quantityInBaseUnits, // Store quantity in base units for checkout
                'price' => $selectedUnit->selling_price,
                'subtotal' => $this->quantityToAdd * $selectedUnit->selling_price,
            ];
        }

        $this->calculateTotalPrice();
        $this->closeUnitModal();
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
        $selectedUnit = ProductUnit::find($item['product_unit_id']);

        if (!$product || !$selectedUnit) {
            session()->flash('error', 'Produk atau satuan tidak valid.');
            return;
        }

        $newQuantityBase = $quantity * $selectedUnit['conversion_factor'];
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $newQuantityBase) {
            throw ValidationException::withMessages([
                'cart_items.' . $index . '.original_quantity_input' => 'Stok produk tidak mencukupi. Stok tersedia: ' . ($totalStockInBaseUnits / $selectedUnit['conversion_factor']) . ' ' . $selectedUnit['name'],
            ]);
        }

        $this->cart_items[$index]['original_quantity_input'] = $quantity;
        $this->cart_items[$index]['quantity'] = $newQuantityBase; // Store in base units
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

        DB::transaction(function () use (&$transaction) {
            $this->invoiceNumber = 'POS-' . Carbon::now()->format('YmdHis');

            $transaction = Transaction::create([
                'type' => 'POS',
                'payment_status' => 'paid',
                'total_price' => $this->total_price,
                'amount_paid' => $this->amount_paid,
                'change' => $this->change,
                'due_date' => null, // POS transactions usually don't have due date
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
                'invoice_number' => $this->invoiceNumber, // Add invoice number
            ]);

            foreach ($this->cart_items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'], // Store selected unit ID
                    'quantity' => $item['quantity'], // Quantity in base units
                    'price' => $item['price'], // Price per selected unit
                ]);

                // Reduce stock from product batches (FEFO)
                $product = Product::find($item['product_id']);
                $remainingQuantity = $item['quantity']; // This is already in base units

                foreach ($product->productBatches()->orderBy('expiration_date', 'asc')->get() as $batch) {
                    if ($remainingQuantity <= 0) break;

                    $deductible = min($remainingQuantity, $batch->stock);
                    $batch->stock -= $deductible;
                    $batch->save();

                    // Record stock movement for sale
                    StockMovement::create([
                        'product_batch_id' => $batch->id,
                        'type' => 'PJ',
                        'quantity' => -$deductible, // Negative for stock out
                        'remarks' => 'Penjualan',
                    ]);

                    $remainingQuantity -= $deductible;
                }
            }
        });

        session()->flash('message', 'Transaksi POS berhasil dicatat dengan No. Nota: ' . $this->invoiceNumber);
        $this->dispatch('transaction-completed', [
            'transactionId' => $transaction->id,
        ]);
        $this->resetAll();
    }

    private function resetAddProductForm()
    {
        $this->selectedProductId = null;
        $this->selectedProductName = '';
        $this->selectedProductUnits = [];
        $this->selectedProductUnitId = null;
        $this->selectedProductUnitSellingPrice = 0;
        $this->selectedProductStock = 0;
        $this->quantityToAdd = 1;
        $this->resetErrorBag(['selectedProductId', 'selectedProductUnitId', 'quantityToAdd']);
    }

    private function resetAll()
    {
        $this->cart_items = [];
        $this->total_price = 0;
        $this->search = '';
        $this->amount_paid = null;
        $this->change = 0;
        $this->invoiceNumber = null; // Reset invoice number
        $this->updateDateTimeAndUser(); // Update date/time and user
        // Reset customer to UMUM
        $umumCustomer = Customer::firstOrCreate(
            ['name' => 'UMUM'],
            ['phone' => null, 'address' => null]
        );
        $this->customer_id = $umumCustomer->id;
        $this->resetAddProductForm();
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

        $products->withSum('productBatches as total_stock', 'stock');

        $products = $products->paginate(10);

        return view('livewire.point-of-sale', compact('customers', 'products'));
    }
}
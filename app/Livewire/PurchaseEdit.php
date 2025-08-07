<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\ProductUnit;
use Livewire\Component;
use Livewire\Attributes\On;
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
    public $purchase_price; // This will be the price per selected unit
    public $stock; // This will be the stock in the selected unit
    public $expiration_date;
    public $lastKnownPurchasePrice; // Properti baru untuk menyimpan harga beli terakhir (dalam satuan dasar)

    public $selectedProductUnits = []; // New property for available units for selected product
    public $selectedProductUnitId; // New property for the currently selected unit ID
    public $selectedProductUnitPurchasePrice; // New property to display purchase price of selected unit

    public $purchase_items = [];
    public $showPriceWarningModal = false;
    public $newSellingPrice;
    public $itemToAddCache = null;

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
            'purchase_items.*.product_unit_id' => 'required|exists:product_units,id', // New rule
            'purchase_items.*.batch_number' => 'nullable|string|max:255',
            'purchase_items.*.purchase_price' => 'required|numeric|min:0',
            'purchase_items.*.stock' => 'required|integer|min:1', // This stock is in base units now
            'purchase_items.*.expiration_date' => 'nullable|date',
        ];
    }

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
        'selectedProductUnitId' => 'required|exists:product_units,id', // New rule
        'batch_number' => 'nullable|string|max:255',
        'purchase_price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:1',
        'expiration_date' => 'nullable|date',
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
        'selectedProductUnitId.required' => 'Satuan produk wajib dipilih.', // New message
        'selectedProductUnitId.exists' => 'Satuan produk tidak valid.', // New message
        'purchase_price.required' => 'Harga beli wajib diisi.',
        'purchase_price.numeric' => 'Harga beli harus berupa angka.',
        'purchase_price.min' => 'Harga beli tidak boleh negatif.',
        'stock.required' => 'Kuantitas wajib diisi.', // Changed from Stok to Kuantitas
        'stock.integer' => 'Kuantitas harus berupa angka bulat.', // Changed from Stok to Kuantitas
        'stock.min' => 'Kuantitas minimal 1.', // Changed from Stok to Kuantitas
        'expiration_date.date' => 'Tanggal kadaluarsa tidak valid.',
        'newSellingPrice.required' => 'Harga jual baru wajib diisi.',
        'newSellingPrice.numeric' => 'Harga jual baru harus berupa angka.',
        'newSellingPrice.min' => 'Harga jual baru tidak boleh lebih rendah dari harga beli.',
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
            $productUnit = $batch->productUnit;
            // If product unit is missing, fall back to the product's base unit
            if (!$productUnit && $batch->product) {
                $productUnit = $batch->product->baseUnit;
            }

            $conversionFactor = $productUnit?->conversion_factor ?: 1;
            $unitName = $productUnit?->name;
            $productUnitId = $productUnit?->id;

            $this->purchase_items[] = [
                'id' => $batch->id, // Keep batch ID for update/delete
                'product_id' => $batch->product_id,
                'product_name' => $batch->product?->name,
                'product_unit_id' => $productUnitId, // Load existing or fallback product unit ID
                'unit_name' => $unitName, // Load existing or fallback unit name
                'conversion_factor' => $conversionFactor, // Load conversion factor
                'batch_number' => $batch->batch_number,
                'purchase_price' => $batch->purchase_price * $conversionFactor, // Convert base price to unit price for display consistency
                'stock' => $batch->stock, // This is already in base unit stock
                'original_stock_input' => $batch->stock / ($conversionFactor ?: 1), // Recalculate original input stock, prevent division by zero
                'expiration_date' => $batch->expiration_date,
                'subtotal' => ($batch->purchase_price * $conversionFactor) * ($batch->stock / ($conversionFactor ?: 1)), // Subtotal based on displayed unit price and stock
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
        $product = Product::with('productUnits')->find($productId); // Load product units
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results

            $this->selectedProductUnits = $product->productUnits->toArray();

            // Automatically select the base unit
            $baseUnit = $product->productUnits->firstWhere('is_base_unit', true);
            if ($baseUnit) {
                $this->selectedProductUnitId = $baseUnit['id'];
                $this->purchase_price = $baseUnit['purchase_price']; // Set initial purchase price to base unit's
                $this->selectedProductUnitPurchasePrice = $baseUnit['purchase_price'];
            } else {
                $this->selectedProductUnitId = null;
                $this->purchase_price = '';
                $this->selectedProductUnitPurchasePrice = '';
            }

            // Ambil harga beli terakhir dari batch produk terbaru (dalam satuan dasar)
            $latestBatch = ProductBatch::where('product_id', $productId)
                                        ->latest('created_at')
                                        ->first();

            if ($latestBatch) {
                // Convert the last known purchase price to the currently selected unit's price
                // This assumes latestBatch->purchase_price is in base unit
                $this->lastKnownPurchasePrice = $latestBatch->purchase_price;
                // If a unit is selected, convert the last known base unit price to that unit's price
                if ($this->selectedProductUnitId) {
                    $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);
                    if ($selectedUnit && $selectedUnit['conversion_factor'] > 0) {
                        $this->purchase_price = $this->lastKnownPurchasePrice * $selectedUnit['conversion_factor'];
                        $this->selectedProductUnitPurchasePrice = $this->purchase_price;
                    }
                }
            } else {
                $this->lastKnownPurchasePrice = null;
            }
        }
    }

    public function updatedSelectedProductUnitId($value)
    {
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $value);
        if ($selectedUnit) {
            $this->purchase_price = $selectedUnit['purchase_price'];
            $this->selectedProductUnitPurchasePrice = $selectedUnit['purchase_price'];

            // If there's a last known base unit purchase price, convert it to the new selected unit's price
            if ($this->lastKnownPurchasePrice !== null) {
                $this->purchase_price = $this->lastKnownPurchasePrice * $selectedUnit['conversion_factor'];
                $this->selectedProductUnitPurchasePrice = $this->purchase_price;
            }
        }
    }

    public function addItem()
    {
        $this->validate($this->itemRules);

        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);

        if (!$selectedUnit) {
            session()->flash('error', 'Satuan produk tidak valid.');
            return;
        }

        // Calculate stock in base units and round it to the nearest integer
        $stockInBaseUnits = round($this->stock * $selectedUnit['conversion_factor']);

        // Get the selling price of the base unit for comparison
        $baseSellingPrice = $product->baseUnit->selling_price ?? 0;

        // Calculate the purchase price in base units for comparison
        $purchasePriceInBaseUnit = $this->purchase_price / $selectedUnit['conversion_factor'];

        // Price warning logic: compare selected unit's purchase price (converted to base) with base unit's selling price
        if ($purchasePriceInBaseUnit > $baseSellingPrice) {
            $expirationDate = $this->expiration_date ?: \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d');

            $this->itemToAddCache = [
                'product_id' => $this->product_id,
                'product_name' => $product->name,
                'product_unit_id' => $this->selectedProductUnitId, // Store selected unit ID
                'unit_name' => $selectedUnit['name'], // Store selected unit name for display
                'conversion_factor' => $selectedUnit['conversion_factor'], // Store conversion factor
                'batch_number' => $this->batch_number,
                'purchase_price' => $this->purchase_price, // Price per selected unit
                'stock' => $stockInBaseUnits, // Stock in base units
                'original_stock_input' => $this->stock, // Store original input for display
                'expiration_date' => $expirationDate,
                'subtotal' => $this->purchase_price * $this->stock, // Subtotal based on selected unit price and input stock
            ];

            $this->newSellingPrice = $baseSellingPrice; // Suggest updating base unit selling price
            $this->showPriceWarningModal = true;
            return;
        }

        // Logika konfirmasi harga lebih rendah (compare base unit purchase price)
        // Convert current purchase price to base unit equivalent for comparison
        $currentBasePurchasePrice = $this->purchase_price / $selectedUnit['conversion_factor'];

        if ($this->lastKnownPurchasePrice !== null && $currentBasePurchasePrice < $this->lastKnownPurchasePrice) {
            $this->dispatch('confirm-lower-price', 'Harga beli per satuan dasar yang diinputkan (' . number_format($currentBasePurchasePrice, 0) . ') lebih rendah dari harga beli terakhir per satuan dasar (' . number_format($this->lastKnownPurchasePrice, 0) . '). Lanjutkan?');
            return; // Hentikan eksekusi sampai ada konfirmasi dari frontend
        }

        $this->confirmedAddItem();
    }

    #[On('confirmedAddItem')]
    public function confirmedAddItem()
    {
        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);

        if (!$selectedUnit) {
            session()->flash('error', 'Satuan produk tidak valid.');
            return;
        }

        // Set expiration_date to 1 year from today if it's empty
        if (empty($this->expiration_date)) {
            $this->expiration_date = \Carbon\Carbon::now()->addYear()->format('Y-m-d');
        }

        // Calculate stock in base units and round it to the nearest integer
        $stockInBaseUnits = round($this->stock * $selectedUnit['conversion_factor']);

        $this->purchase_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'product_unit_id' => $this->selectedProductUnitId, // Store selected unit ID
            'unit_name' => $selectedUnit['name'], // Store selected unit name for display
            'conversion_factor' => $selectedUnit['conversion_factor'], // Store conversion factor
            'batch_number' => $this->batch_number,
            'purchase_price' => $this->purchase_price, // Price per selected unit
            'stock' => $stockInBaseUnits, // Stock in base units
            'original_stock_input' => $this->stock, // Store original input for display
            'expiration_date' => $this->expiration_date,
            'subtotal' => $this->purchase_price * $this->stock, // Subtotal based on selected unit price and input stock
        ];

        $this->calculateTotalPurchasePrice();
        $this->resetItemForm();
    }

    public function updatePriceAndAddItem()
    {
        // Calculate the minimum selling price in base unit
        $minSellingPrice = $this->itemToAddCache['purchase_price'] / $this->itemToAddCache['conversion_factor'];

        $this->validate([
            'newSellingPrice' => 'required|numeric|min:' . $minSellingPrice
        ]);

        $product = Product::find($this->itemToAddCache['product_id']);
        if ($product && $product->baseUnit) {
            $product->baseUnit->selling_price = $this->newSellingPrice;
            $product->baseUnit->save();
        }

        $this->purchase_items[] = $this->itemToAddCache;
        $this->calculateTotalPurchasePrice();

        $this->closePriceWarningModal();
        $this->resetItemForm();
    }

    public function closePriceWarningModal()
    {
        $this->showPriceWarningModal = false;
        $this->itemToAddCache = null;
        $this->newSellingPrice = null;
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
                $batchNumber = empty($item['batch_number']) ? '-' : $item['batch_number'];

                // Always convert to base unit values before saving
                $selectedUnit = ProductUnit::find($item['product_unit_id']);
                $stockInBaseUnits = $item['original_stock_input'] * $selectedUnit->conversion_factor;
                $purchasePriceInBaseUnit = $item['purchase_price'] / $selectedUnit->conversion_factor;

                $batchData = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'],
                    'batch_number' => $batchNumber,
                    'purchase_price' => $purchasePriceInBaseUnit,
                    'stock' => $stockInBaseUnits,
                    'expiration_date' => $item['expiration_date'],
                ];

                if (isset($item['id'])) {
                    $batch = ProductBatch::find($item['id']);
                    if ($batch) {
                        $batch->update($batchData);
                        $updatedBatchIds[] = $batch->id;
                    }
                } else {
                    $batch = ProductBatch::create($batchData);
                    $updatedBatchIds[] = $batch->id;
                }
            }

            // Delete batches that were removed from the list
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
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductUnits = []; // Reset new properties
        $this->selectedProductUnitId = null; // Reset new properties
        $this->selectedProductUnitPurchasePrice = ''; // Reset new properties
        $this->resetErrorBag(['product_id', 'selectedProductUnitId', 'batch_number', 'purchase_price', 'stock', 'expiration_date']);
    }

    public function render()
    {
        $suppliers = Supplier::all();
        return view('livewire.purchase-edit', compact('suppliers'));
    }
}
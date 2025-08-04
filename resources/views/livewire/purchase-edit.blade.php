<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Edit Pembelian #{{ $invoice_number }}</h2>

        <!-- Purchase Details -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Informasi Utama</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <select id="supplier_id" wire:model="supplier_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" disabled>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Invoice</label>
                    <input type="text" id="invoice_number" wire:model="invoice_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('invoice_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian</label>
                    <input type="date" id="purchase_date" wire:model.live="purchase_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('purchase_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jatuh Tempo (Opsional)</label>
                    <input type="date" id="due_date" wire:model="due_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Add Purchase Item -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Tambah Item</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative">
                    <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk</label>
                    <input type="text" id="searchProduct" wire:model.live="searchProduct" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @if(!empty($searchResults))
                        <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-800 dark:border-gray-600">
                            @foreach($searchResults as $product)
                                <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                    {{ $product->name }} ({{ $product->sku }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($selectedProductName))
                        <p class="text-green-600 text-sm mt-2 dark:text-green-400">Terpilih: {{ $selectedProductName }}</p>
                    @endif
                    @error('product_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="selectedProductUnitId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan Pembelian</label>
                    <select id="selectedProductUnitId" wire:model.live="selectedProductUnitId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="">Pilih Satuan</option>
                        @foreach($selectedProductUnits as $unit)
                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                        @endforeach
                    </select>
                    @error('selectedProductUnitId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="batch_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Batch</label>
                    <input type="text" id="batch_number" wire:model="batch_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('batch_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Beli per Satuan</label>
                    <input type="number" step="0.01" id="purchase_price" wire:model="purchase_price" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('purchase_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kuantitas (dalam Satuan Terpilih)</label>
                    <input type="number" id="stock" wire:model="stock" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('stock') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="expiration_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Kadaluarsa (Opsional)</label>
                    <input type="date" id="expiration_date" wire:model="expiration_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('expiration_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="text-right mt-6">
                <button type="button" wire:click="addItem()" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600">Tambah Item ke Daftar</button>
            </div>
        </div>

        <!-- Purchase Items List -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Daftar Item</h3>
            <div class="space-y-4">
                @forelse($purchase_items as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item['product_name'] }}</p>
                            <span class="text-sm text-gray-600 dark:text-gray-400">( {{ $item['batch_number'] ?: '-' }} )</span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Tanggal Expire:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $item['expiration_date'] ? \Carbon\Carbon::parse($item['expiration_date'])->format('d/m/Y') : '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Harga Beli:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">Rp {{ number_format($item['purchase_price'], 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Stok:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $item['original_stock_input'] }} {{ $item['unit_name'] }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">Subtotal:</span>
                                <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0) }}</span>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">Belum ada item yang ditambahkan.</p>
                @endforelse
            </div>
            <div class="mt-6 pt-4 border-t-2 border-gray-200 dark:border-gray-600 flex justify-between items-center">
                <span class="text-xl font-bold text-gray-900 dark:text-white">Total</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($total_purchase_price, 0) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <a href="{{ route('purchases.show', $purchaseId) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
            <button type="button" wire:click="savePurchase()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                Update Pembelian
            </button>
        </div>
    </div>
</div>

<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Edit Pembelian #{{ $invoice_number }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700">
        <div class="mb-4">
            <label for="supplier_id" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Supplier:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="supplier_id" wire:model="supplier_id" disabled>
                <option value="">Pilih Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
            @error('supplier_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="invoice_number" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Nomor Invoice:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="invoice_number" wire:model="invoice_number">
            @error('invoice_number') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="purchase_date" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Pembelian:</label>
            <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="purchase_date" wire:model.live="purchase_date">
            @error('purchase_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Jatuh Tempo (Opsional):</label>
            <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="due_date" wire:model="due_date">
            @error('due_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Item Pembelian</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4">
                <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Produk:</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="product_id" wire:model="product_id">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="batch_number" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Nomor Batch (Opsional):</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="batch_number" wire:model="batch_number">
                @error('batch_number') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="purchase_price" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Harga Beli:</label>
                <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="purchase_price" wire:model="purchase_price">
                @error('purchase_price') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="stock" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Stok:</label>
                <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="stock" wire:model="stock">
                @error('stock') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="expiration_date" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Kadaluarsa (Opsional):</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="expiration_date" wire:model="expiration_date">
                @error('expiration_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
        </div>
        <button type="button" wire:click="addItem()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-green-600 dark:hover:bg-green-700">Tambah Item</button>
    </div>

    <hr class="my-6 border-gray-300 dark:border-gray-600">

    <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Item Pembelian</h3>
    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nomor Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kadaluarsa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($purchase_items as $index => $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item['product_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item['batch_number'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($item['purchase_price'], 2) }}</span>
                            </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item['stock'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item['expiration_date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($item['subtotal'], 2) }}</span>
                            </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View for Items -->
    <div class="block md:hidden space-y-4 mb-4">
        @forelse($purchase_items as $index => $item)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $item['product_name'] }}</span>
                <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Batch:</span> {{ $item['batch_number'] }}
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Harga Beli:</span> Rp {{ number_format($item['purchase_price'], 2) }}
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Stok:</span> {{ $item['stock'] }}
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Tgl Kadaluarsa:</span> {{ $item['expiration_date'] }}
            </div>
            <div class="text-gray-800 dark:text-gray-100 font-bold mt-2">
                Subtotal: Rp {{ number_format($item['subtotal'], 2) }}
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400">Belum ada item pembelian.</p>
        @endforelse
    </div>

    <div class="text-right text-2xl font-bold mb-4 dark:text-gray-100">Total Pembelian: Rp {{ number_format($total_purchase_price, 2) }}</div>
    <button type="button" wire:click="savePurchase()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700">Update Pembelian</button>

</div>

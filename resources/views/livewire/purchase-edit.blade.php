<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Edit Pembelian #{{ $invoice_number }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label for="supplier_id" class="block text-gray-700 text-sm font-bold mb-2">Supplier:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="supplier_id" wire:model="supplier_id" disabled>
                <option value="">Pilih Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
            @error('supplier_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="invoice_number" class="block text-gray-700 text-sm font-bold mb-2">Nomor Invoice:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="invoice_number" wire:model="invoice_number">
            @error('invoice_number') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="purchase_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Pembelian:</label>
            <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="purchase_date" wire:model.live="purchase_date">
            @error('purchase_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Jatuh Tempo (Opsional):</label>
            <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="due_date" wire:model="due_date">
            @error('due_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>

        <hr class="my-6">

        <h3 class="text-xl font-semibold mb-4">Item Pembelian</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4">
                <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2">Produk:</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="product_id" wire:model="product_id">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="batch_number" class="block text-gray-700 text-sm font-bold mb-2">Nomor Batch (Opsional):</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="batch_number" wire:model="batch_number">
                @error('batch_number') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="purchase_price" class="block text-gray-700 text-sm font-bold mb-2">Harga Beli:</label>
                <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="purchase_price" wire:model="purchase_price">
                @error('purchase_price') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="stock" class="block text-gray-700 text-sm font-bold mb-2">Stok:</label>
                <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="stock" wire:model="stock">
                @error('stock') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="expiration_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Kadaluarsa (Opsional):</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="expiration_date" wire:model="expiration_date">
                @error('expiration_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
        </div>
        <button type="button" wire:click="addItem()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Tambah Item</button>
    </div>

    <hr class="my-6">

    <h3 class="text-xl font-semibold mb-4">Item Pembelian</h3>
    @if(count($purchase_items) > 0)
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kadaluarsa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($purchase_items as $index => $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['product_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['batch_number'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item['purchase_price'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['stock'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['expiration_date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item['subtotal'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-right text-2xl font-bold mb-4">Total Pembelian: Rp {{ number_format($total_purchase_price, 2) }}</div>
        <button type="button" wire:click="savePurchase()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Pembelian</button>
    @else
        <p class="text-gray-600">Belum ada item pembelian.</p>
    @endif
</div>

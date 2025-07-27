<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Edit Transaksi #{{ $transactionId }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Tipe Transaksi:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="type" wire:model="type">
                <option value="pos">POS</option>
                <option value="invoice">Invoice</option>
            </select>
            @error('type') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="payment_status" class="block text-gray-700 text-sm font-bold mb-2">Status Pembayaran:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="payment_status" wire:model="payment_status">
                <option value="paid">Lunas</option>
                <option value="unpaid">Belum Lunas</option>
            </select>
            @error('payment_status') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="customer_id" class="block text-gray-700 text-sm font-bold mb-2">Customer (Opsional):</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="customer_id" wire:model="customer_id">
                <option value="">Pilih Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            @error('customer_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Jatuh Tempo (Opsional):</label>
            <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="due_date" wire:model="due_date">
            @error('due_date') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>

        <hr class="my-6">

        <h3 class="text-xl font-semibold mb-4">Item Transaksi</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4 relative">
                <label for="searchProduct" class="block text-gray-700 text-sm font-bold mb-2">Produk:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="searchProduct" placeholder="Cari Produk..." wire:model.live="searchProduct">
                <input type="hidden" wire:model="product_id">
                @if(!empty($selectedProductName))
                    <p class="text-gray-600 text-sm mt-1">Produk Terpilih: {{ $selectedProductName }}</p>
                @endif
                @error('product_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror

                @if(!empty($searchResults))
                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto">
                        @foreach($searchResults as $product)
                            <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                {{ $product->name }} ({{ $product->sku }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="mb-4">
                <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Kuantitas:</label>
                <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="quantity" wire:model="quantity">
                @error('quantity') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Harga Satuan:</label>
                <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="price" wire:model="price">
                @error('price') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
        </div>
        <button type="button" wire:click="addItem()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Tambah Item</button>
    </div>

    <hr class="my-6">

    <h3 class="text-xl font-semibold mb-4">Item Transaksi</h3>
    @if(count($transaction_items) > 0)
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transaction_items as $index => $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['product_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['quantity'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item['price'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item['subtotal'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-right text-2xl font-bold mb-4">Total: Rp {{ number_format($total_price, 2) }}</div>
        <button type="button" wire:click="saveTransaction()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Transaksi</button>
    @else
        <p class="text-gray-600">Belum ada item transaksi.</p>
    @endif
</div>

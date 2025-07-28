<div class="container mx-auto p-4" x-data="{}" x-init="$nextTick(() => $refs.searchInput.focus())" @focus-search-input.window="$nextTick(() => $refs.searchInput.focus())">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Point of Sale (POS)</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Column: Product Search & Cart -->
        <div class="md:col-span-2 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
                <div>Tanggal: {{ $currentDateTime }}</div>
                <div>Kasir: {{ $loggedInUser }}</div>
            </div>

            <h3 class="text-xl font-semibold mb-4">Pencarian Produk</h3>
            <div class="mb-4 relative">
                <input type="text" wire:model.live="search" placeholder="Cari Produk (Nama/SKU)..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    x-ref="searchInput"
                    wire:keydown.arrow-up="decrementHighlight"
                    wire:keydown.arrow-down="incrementHighlight"
                    wire:keydown.enter="selectHighlightedProduct"
                >
                @if(!empty($searchResults))
                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto">
                        @foreach($searchResults as $index => $product)
                            <li wire:click="addProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 {{ $index === $highlightedIndex ? 'bg-blue-200' : '' }}">
                                {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $product->total_stock }}
                            </li>
                        @endforeach
                    </ul>
                @endif
                @error('search') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>

            <h3 class="text-xl font-semibold mb-4">Keranjang Belanja</h3>
            @if(count($cart_items) > 0)
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4">
                    <div class="overflow-x-auto"> <!-- Added this div -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($cart_items as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['product_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap currency-cell">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($item['price'], 2) }}</span>
                            </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <button type="button" wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-2 rounded-l focus:outline-none focus:shadow-outline">-</button>
                                        <input type="number" wire:change="updateQuantity({{ $index }}, $event.target.value)" value="{{ $item['quantity'] }}" min="1" class="shadow appearance-none border-t border-b border-gray-200 w-16 py-1 px-2 text-center text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <button type="button" wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-2 rounded-r focus:outline-none focus:shadow-outline">+</button>
                                    </div>
                                    @error('cart_items.' . $index . '.quantity') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap currency-cell">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($item['subtotal'], 2) }}</span>
                            </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full">Hapus</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">Keranjang belanja kosong.</p>
            @endif
        </div>

        <!-- Right Column: Payment & Checkout -->
        <div class="md:col-span-1 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h3 class="text-xl font-semibold mb-4">Detail Pembayaran</h3>

            <div class="mb-4">
                <label for="customer_id" class="block text-gray-700 text-sm font-bold mb-2">Customer:</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="customer_id" wire:model="customer_id">
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>

            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Total Harga:</p>
                <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($total_price, 2) }}</p>
            </div>

            <div class="mb-4">
                <label for="amount_paid" class="block text-gray-700 text-sm font-bold mb-2">Jumlah Bayar:</label>
                <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="amount_paid" wire:model.live="amount_paid">
                @error('amount_paid') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>

            <div class="mb-6">
                <p class="text-gray-700 text-sm font-bold">Kembalian:</p>
                <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($change, 2) }}</p>
            </div>

            <button type="button" wire:click="checkout()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg w-full focus:outline-none focus:shadow-outline">
                Checkout
            </button>
        </div>
    </div>
</div>

@script
<script>
    Livewire.on('transaction-completed', (event) => {
        const { transactionId } = event[0];
        const printReceipt = confirm('Transaksi berhasil! Apakah Anda ingin mencetak struk?');

        if (printReceipt) {
            const url = `/transactions/${transactionId}/receipt`;
            window.open(url, '_blank');
        }
    });
</script>
@endscript

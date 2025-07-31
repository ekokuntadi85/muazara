<div class="w-full h-screen flex flex-col md:flex-row bg-gray-100 dark:bg-gray-900" x-data="{ paymentModal: false, activeTab: 'products' }">

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-md p-4 z-10">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Point of Sale</h1>
                <div class="text-sm text-right">
                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $loggedInUser }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ $currentDateTime }}</p>
                </div>
            </div>
            <div class="mt-4 relative">
                <svg class="w-6 h-6 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari Produk (Nama/SKU)..." 
                    class="pl-11 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
        </header>

        <!-- Tabs for Mobile -->
        <div class="block md:hidden bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <nav class="flex justify-around text-center text-sm font-medium">
                <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'" class="flex-1 py-3 border-b-2 focus:outline-none">
                    Produk
                </button>
                <button @click="activeTab = 'cart'" :class="activeTab === 'cart' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'" class="flex-1 py-3 border-b-2 focus:outline-none">
                    Keranjang ({{ count($cart_items) }})
                </button>
            </nav>
        </div>

        <!-- Product Grid (Mobile & Desktop) -->
        <main class="p-4 flex-1 overflow-y-auto pb-32 md:pb-4" x-show="activeTab === 'products' || window.innerWidth >= 768">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                @forelse ($products as $product)
                    <div wire:click="addProduct({{ $product->id }})" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 flex flex-col justify-between cursor-pointer hover:scale-105 transform transition-transform duration-200">
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white truncate">{{ $product->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Stok: {{ $product->total_stock ?? 0 }}</p>
                        </div>
                        <p class="text-right font-semibold text-blue-600 dark:text-blue-400 mt-2">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <div class="col-span-full text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Produk tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </main>

        <!-- Cart (Mobile - below product grid) -->
        <div class="block md:hidden p-4 space-y-3 flex-1 overflow-y-auto mb-32" x-show="activeTab === 'cart'">
            @if(count($cart_items) > 0)
                @foreach($cart_items as $index => $item)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $item['product_name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Harga: Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex items-center space-x-2">
                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-600 text-lg font-bold">-</button>
                                <input type="text" value="{{ $item['quantity'] }}" readonly class="w-12 text-center bg-transparent font-semibold text-gray-900 dark:text-white">
                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-600 text-lg font-bold">+</button>
                                <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 ml-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <p class="font-bold text-gray-900 dark:text-white">Subtotal: Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-10">
                    <p class="text-gray-500 dark:text-gray-400">Keranjang belanja kosong.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Cart & Payment (Desktop) / Payment (Mobile - Sticky) -->
    <div class="w-full md:w-1/3 lg:w-1/4 bg-white dark:bg-gray-800 shadow-lg flex flex-col md:static fixed bottom-0 left-0 right-0 z-20">
        <!-- Cart (Desktop Only) -->
        <div class="hidden md:block p-4 flex-1 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Keranjang</h2>
            <div class="space-y-3">
                @if(count($cart_items) > 0)
                    @foreach($cart_items as $index => $item)
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $item['product_name'] }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-300">{{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                            <button wire:click="removeItem({{ $index }})" class="ml-3 text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Keranjang kosong.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Section (Always visible, sticky on mobile) -->
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 md:shadow-none md:static">
            <div class="flex justify-between items-center mb-2">
                <span class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0, ',', '.') }}</span>
            </div>
            <button @click="paymentModal = true" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg text-lg hover:bg-blue-700" >
                Bayar ({{ count($cart_items) }} item)
            </button>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="paymentModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div @click.away="paymentModal = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Pembayaran</h2>
            <div class="mb-4">
                <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan</label>
                <select id="customer_id" wire:model="customer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <option value="">Walk-in Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Tagihan</label>
                <p class="text-3xl font-bold mt-1 text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0, ',', '.') }}</p>
            </div>
            <div class="mb-6">
                <label for="amount_paid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Bayar</label>
                <input type="number" id="amount_paid" wire:model.live="amount_paid" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 text-lg p-2">
                @error('amount_paid') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-between items-center mb-6">
                <span class="text-gray-700 dark:text-gray-300">Kembalian</span>
                <span class="text-xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($change, 0, ',', '.') }}</span>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <button @click="paymentModal = false" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-2 sm:mt-0">Batal</button>
                <button wire:click="checkout" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Selesaikan Transaksi</button>
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
            // Always redirect the current window to a new POS session
            window.location.href = '{{ route('pos.index') }}';
        });
    </script>
    @endscript
</div>

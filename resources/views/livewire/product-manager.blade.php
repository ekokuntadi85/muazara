<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-800 dark:border-red-700 dark:text-red-200" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 space-y-4 md:space-y-0">
        <div class="relative w-full md:w-1/3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." class="shadow appearance-none border rounded py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        </div>
        <a href="{{ route('products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700">Tambah Produk</a>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Harga Jual</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Satuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($products as $product)
                    <tr class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" onclick="window.location='{{ route('products.show', $product->id) }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($product->selling_price, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->unit->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->total_stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($products as $product)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="window.location='{{ route('products.show', $product->id) }}'">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $product->name }}</h3>
            <div class="flex justify-between items-center text-sm mb-1">
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                <span class="font-semibold text-gray-900 dark:text-white">Stok: {{ $product->total_stock }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600 dark:text-gray-400">SKU: {{ $product->sku }}</span>
                <span class="text-gray-600 dark:text-gray-400">{{ $product->unit->name }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <p class="text-gray-600 dark:text-gray-400">Tidak ada produk ditemukan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>

<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Detail Produk: {{ $product->name }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Nama Produk:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $product->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">SKU:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $product->sku }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Harga Jual:</p>
                <p class="text-gray-900 dark:text-gray-200">Rp {{ number_format($product->selling_price, 2) }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Kategori:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $product->category->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Satuan:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $product->unit->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Total Stok:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $product->total_stock }}</p>
            </div>
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Sejarah Stok (Pembelian)</h3>
        @if(count($product->productBatches) > 0)
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal Pembelian</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Harga Beli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($product->productBatches as $batch)
                            <tr class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" onclick="window.location='{{ route('purchases.show', $batch->purchase->id) }}'">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $batch->purchase->purchase_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $batch->purchase->supplier->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($batch->purchase_price, 2) }}</span>
                            </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $batch->stock }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">Tidak ada sejarah stok untuk produk ini.</p>
        @endif

        <div class="flex justify-end mt-4">
            <a href="{{ route('products.edit', $product->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2 dark:bg-green-600 dark:hover:bg-green-700">Edit Produk</a>
            <button wire:click="deleteProduct()" wire:confirm="Apakah Anda yakin ingin menghapus produk ini?" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full mr-2 dark:bg-red-600 dark:hover:bg-red-700">Hapus Produk</button>
        </div>
    </div>
</div>
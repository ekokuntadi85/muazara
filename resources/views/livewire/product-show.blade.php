<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Detail Produk: {{ $product->name }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Nama Produk:</p>
                <p class="text-gray-900">{{ $product->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">SKU:</p>
                <p class="text-gray-900">{{ $product->sku }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Harga Jual:</p>
                <p class="text-gray-900">Rp {{ number_format($product->selling_price, 2) }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Kategori:</p>
                <p class="text-gray-900">{{ $product->category->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Satuan:</p>
                <p class="text-gray-900">{{ $product->unit->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Total Stok:</p>
                <p class="text-gray-900">{{ $product->total_stock }}</p>
            </div>
        </div>

        <hr class="my-6">

        <h3 class="text-xl font-semibold mb-4">Sejarah Stok (Pembelian)</h3>
        @if(count($product->productBatches) > 0)
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pembelian</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($product->productBatches as $batch)
                            <tr class="cursor-pointer hover:bg-gray-100" onclick="window.location='{{ route('purchases.show', $batch->purchase->id) }}'">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $batch->purchase->purchase_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $batch->purchase->supplier->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($batch->purchase_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $batch->stock }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-gray-600">Tidak ada sejarah stok untuk produk ini.</p>
        @endif

        <div class="flex justify-end mt-4">
            <a href="{{ route('products.edit', $product->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2">Edit Produk</a>
            <button wire:click="deleteProduct()" onclick="confirm('Apakah Anda yakin ingin menghapus produk ini? Semua batch terkait juga akan dihapus.') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full">Hapus Produk</button>
        </div>
    </div>
</div>
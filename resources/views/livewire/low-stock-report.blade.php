<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <style>
        @media (max-width: 768px) {
            .mobile-card table, .mobile-card thead, .mobile-card tbody, .mobile-card th, .mobile-card td, .mobile-card tr {
                display: block;
            }
            .mobile-card thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .mobile-card tr {
                border: 1px solid #ccc;
                border-radius: 0.5rem;
                margin-bottom: 1rem;
            }
            .mobile-card td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            .mobile-card td:before {
                position: absolute;
                top: 6px;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
            }
        }
    </style>

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Laporan Stok Menipis</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <div class="mb-4">
            <label for="stock_threshold" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Ambang Batas Stok:</label>
            <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="stock_threshold" wire:model.live="stock_threshold" min="0">
        </div>
    </div>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700 mobile-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Stok</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($products as $product)
                    <tr class="dark:hover:bg-gray-700">
                        <td data-label="Produk" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <a href="{{ route('products.show', $product->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-500">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td data-label="SKU" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->sku }}</td>
                        <td data-label="Total Stok" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->productBatches->sum('stock') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada produk dengan stok menipis.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
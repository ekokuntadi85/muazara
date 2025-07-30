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

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Laporan Stok Kedaluwarsa</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <div class="w-1/3">
                <label for="expiry_threshold_months" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Ambang Batas Kedaluwarsa:</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="expiry_threshold_months" wire:model.live="expiry_threshold_months">
                    <option value="1">1 Bulan</option>
                    <option value="2">2 Bulan</option>
                    <option value="3">3 Bulan</option>
                    <option value="4">4 Bulan</option>
                    <option value="5">5 Bulan</option>
                    <option value="6">6 Bulan</option>
                </select>
            </div>
            <input type="text" wire:model.live="search" placeholder="Cari produk atau batch..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-1/3 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
        </div>
        <div class="flex justify-end">
            <a href="{{ route('reports.expiring-stock.print', ['expiry_threshold_months' => $expiry_threshold_months]) }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">Cetak Laporan</a>
        </div>
    </div>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700 mobile-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nomor Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal Kedaluwarsa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Harga Beli</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($productBatches as $batch)
                    <tr class="dark:hover:bg-gray-700">
                        <td data-label="Produk" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <a href="{{ route('products.show', $batch->product->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-500">
                                {{ $batch->product->name ?? 'N/A' }}
                            </a>
                        </td>
                        <td data-label="Nomor Batch" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $batch->batch_number }}</td>
                        <td data-label="Tanggal Kedaluwarsa" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d') }}</td>
                        <td data-label="Stok" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $batch->stock }}</td>
                        <td data-label="Harga Beli" class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($batch->purchase_price, 2) }}</span>
                        </td>
                        <td data-label="Supplier" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $batch->purchase->supplier->name ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada stok kedaluwarsa dalam periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $productBatches->links() }}
    </div>
</div>
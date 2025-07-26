<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Laporan Stok Kedaluwarsa</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label for="expiry_threshold_months" class="block text-gray-700 text-sm font-bold mb-2">Ambang Batas Kedaluwarsa:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="expiry_threshold_months" wire:model.live="expiry_threshold_months">
                <option value="2">2 Bulan</option>
                <option value="3">3 Bulan</option>
                <option value="4">4 Bulan</option>
                <option value="5">5 Bulan</option>
                <option value="6">6 Bulan</option>
            </select>
        </div>
    </div>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kedaluwarsa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($productBatches as $batch)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('products.show', $batch->product->id) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $batch->product->name ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $batch->batch_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $batch->stock }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($batch->purchase_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $batch->purchase->supplier->name ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Tidak ada stok kedaluwarsa dalam periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
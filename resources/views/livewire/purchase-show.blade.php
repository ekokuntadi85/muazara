<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Detail Pembelian #{{ $purchase->invoice_number }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Supplier:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $purchase->supplier->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Tanggal Pembelian:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $purchase->purchase_date }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Nomor Invoice:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $purchase->invoice_number }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Total Pembelian:</p>
                <p class="text-gray-900 dark:text-gray-200">Rp {{ number_format($purchase->total_price, 2) }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Tanggal Jatuh Tempo:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ $purchase->due_date ?? '-' }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Status Pembayaran:</p>
                <p class="text-gray-900 dark:text-gray-200">{{ ucfirst($purchase->payment_status) }}</p>
            </div>
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        @if ($purchase->payment_status === 'unpaid')
            <div class="flex justify-end mb-4">
                <button wire:click="markAsPaid()" onclick="confirm('Apakah Anda yakin ingin menandai pembelian ini sebagai lunas?') || event.stopImmediatePropagation()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700">Tandai Lunas</button>
            </div>
        @endif

        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Item Pembelian</h3>
        <!-- Desktop Table View -->
        <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nomor Batch</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Harga Beli</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok Awal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok Saat Ini</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tgl Kadaluarsa</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach($purchase->productBatches as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->batch_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($item->purchase_price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->getOriginal('stock') }}</td> <!-- Assuming original stock is needed -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->stock }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->expiration_date }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View for Items -->
        <div class="block md:hidden space-y-4 mb-4">
            @forelse($purchase->productBatches as $item)
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">{{ $item->product->name }}</div>
                <div class="text-gray-700 dark:text-gray-200 mb-1">
                    <span class="font-medium">Batch:</span> {{ $item->batch_number }}
                </div>
                <div class="text-gray-700 dark:text-gray-200 mb-1">
                    <span class="font-medium">Harga Beli:</span> Rp {{ number_format($item->purchase_price, 2) }}
                </div>
                <div class="text-gray-700 dark:text-gray-200 mb-1">
                    <span class="font-medium">Stok Awal:</span> {{ $item->getOriginal('stock') }}
                </div>
                <div class="text-gray-700 dark:text-gray-200 mb-1">
                    <span class="font-medium">Stok Saat Ini:</span> {{ $item->stock }}
                </div>
                <div class="text-gray-700 dark:text-gray-200">
                    <span class="font-medium">Tgl Kadaluarsa:</span> {{ $item->expiration_date }}
                </div>
            </div>
            @empty
            <p class="text-gray-600 dark:text-gray-400">Tidak ada item pembelian untuk transaksi ini.</p>
            @endforelse
        </div>

        <div class="flex flex-col md:flex-row justify-end mt-4 space-y-2 md:space-y-0 md:space-x-2">
            <a href="{{ route('purchases.edit', $purchase->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full w-full md:w-auto dark:bg-green-600 dark:hover:bg-green-700">Edit Pembelian</a>
            <button wire:click="deletePurchase()" onclick="confirm('Apakah Anda yakin ingin menghapus pembelian ini? Semua item batch terkait juga akan dihapus.') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full w-full md:w-auto dark:bg-red-600 dark:hover:bg-red-700">Hapus Pembelian</button>
        </div>
    </div>
</div>

<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Detail Pembelian #{{ $purchase->invoice_number }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Supplier:</p>
                <p class="text-gray-900">{{ $purchase->supplier->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Tanggal Pembelian:</p>
                <p class="text-gray-900">{{ $purchase->purchase_date }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Nomor Invoice:</p>
                <p class="text-gray-900">{{ $purchase->invoice_number }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Total Pembelian:</p>
                <p class="text-gray-900">Rp {{ number_format($purchase->total_price, 2) }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Tanggal Jatuh Tempo:</p>
                <p class="text-gray-900">{{ $purchase->due_date ?? '-' }}</p>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-bold">Status Pembayaran:</p>
                <p class="text-gray-900">{{ ucfirst($purchase->payment_status) }}</p>
            </div>
        </div>

        <hr class="my-6">

        @if ($purchase->payment_status === 'unpaid')
            <div class="flex justify-end mb-4">
                <button wire:click="markAsPaid()" onclick="confirm('Apakah Anda yakin ingin menandai pembelian ini sebagai lunas?') || event.stopImmediatePropagation()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">Tandai Lunas</button>
            </div>
        @endif

        <h3 class="text-xl font-semibold mb-4">Item Pembelian</h3>
        @if(count($purchase->productBatches) > 0)
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4">
                <div class="overflow-x-auto"> <!-- Added for responsiveness -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Batch</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Awal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kadaluarsa</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchase->productBatches as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->batch_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap currency-cell">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($item->purchase_price, 2) }}</span>
                            </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->getOriginal('stock') }}</td> <!-- Assuming original stock is needed -->
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->stock }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->expiration_date }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-gray-600">Tidak ada item pembelian untuk transaksi ini.</p>
        @endif

        <div class="flex justify-end mt-4">
            <a href="{{ route('purchases.edit', $purchase->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2">Edit Pembelian</a>
            <button wire:click="deletePurchase()" onclick="confirm('Apakah Anda yakin ingin menghapus pembelian ini? Semua item batch terkait juga akan dihapus.') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full">Hapus Pembelian</button>
        </div>
    </div>
</div>

<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Detail Transaksi #{{ $transaction->id }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Tipe Transaksi:</p>
            <p class="text-gray-900">{{ $transaction->type }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Status Pembayaran:</p>
            <p class="text-gray-900">{{ $transaction->payment_status }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Total Harga:</p>
            <p class="text-gray-900">Rp {{ number_format($transaction->total_price, 2) }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Tanggal Jatuh Tempo:</p>
            <p class="text-gray-900">{{ $transaction->due_date ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Customer:</p>
            <p class="text-gray-900">{{ $transaction->customer->name ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">User:</p>
            <p class="text-gray-900">{{ $transaction->user->name }}</p>
        </div>

        <hr class="my-6">

        <h3 class="text-xl font-semibold mb-4">Item Transaksi</h3>
        @if(count($transaction->transactionDetails) > 0)
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4">
                <div class="overflow-x-auto"> <!-- Added for responsiveness -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transaction->transactionDetails as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-gray-600">Tidak ada item transaksi untuk transaksi ini.</p>
        @endif

        <div class="flex justify-end mt-4">
            @can('manage-sales')
            <a href="{{ route('transactions.print-receipt', $transaction->id) }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full mr-2">Cetak Struk</a>
            <a href="{{ route('transactions.print-invoice', $transaction->id) }}" target="_blank" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-full mr-2">Cetak Invoice</a>
            @endcan
            @can('delete-sales')
            <a href="{{ route('transactions.edit', $transaction->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2">Edit Transaksi</a>
            <button wire:click="deleteTransaction()" onclick="confirm('Apakah Anda yakin ingin menghapus transaksi ini? Semua detail transaksi terkait juga akan dihapus.') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full">
                Hapus Transaksi
            </button>
            @endcan
        </div>
    </div>
</div>
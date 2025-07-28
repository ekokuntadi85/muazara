<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Detail Transaksi #{{ $transaction->id }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Tipe Transaksi:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $transaction->type }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Status Pembayaran:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $transaction->payment_status }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Total Harga:</p>
            <p class="text-gray-900 dark:text-gray-200">Rp {{ number_format($transaction->total_price, 2) }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Tanggal Jatuh Tempo:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $transaction->due_date ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Customer:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $transaction->customer->name ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">User:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $transaction->user->name }}</p>
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Item Transaksi</h3>
        @if(count($transaction->transactionDetails) > 0)
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg mb-4 dark:border-gray-700">
                <div class="overflow-x-auto"> <!-- Added for responsiveness -->
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kuantitas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Harga Satuan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($transaction->transactionDetails as $item)
                            <tr class="dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">Tidak ada item transaksi untuk transaksi ini.</p>
        @endif

        <div class="flex justify-end mt-4">
            @can('manage-sales')
            <a href="{{ route('transactions.print-receipt', $transaction->id) }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full mr-2 dark:bg-blue-600 dark:hover:bg-blue-700">Cetak Struk</a>
            <a href="{{ route('transactions.print-invoice', $transaction->id) }}" target="_blank" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-full mr-2 dark:bg-indigo-600 dark:hover:bg-indigo-700">Cetak Invoice</a>
            @endcan
            @can('delete-sales')
            <a href="{{ route('transactions.edit', $transaction->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2 dark:bg-green-600 dark:hover:bg-green-700">Edit Transaksi</a>
            <button wire:click="deleteTransaction()" onclick="confirm('Apakah Anda yakin ingin menghapus transaksi ini? Semua detail transaksi terkait juga akan dihapus.') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full dark:bg-red-600 dark:hover:bg-red-700">
                Hapus Transaksi
            </button>
            @endcan
        </div>
    </div>
</div>
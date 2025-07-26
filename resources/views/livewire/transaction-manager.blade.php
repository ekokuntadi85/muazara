<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari transaksi..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-1/3">
        <a href="{{ route('transactions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Tambah Transaksi</a>
    </div>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Penjualan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Nota</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pembelian</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($transactions as $transaction)
                <tr class="cursor-pointer hover:bg-gray-100" onclick="window.location='{{ route('transactions.show', $transaction->id) }}'">
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->invoice_number ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->customer->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($transaction->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
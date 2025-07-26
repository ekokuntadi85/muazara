<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Piutang (Accounts Receivable)</h2>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Transaksi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Jatuh Tempo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($receivables as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->invoice_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->customer->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($transaction->total_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->due_date ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="markAsPaid({{ $transaction->id }})" onclick="confirm('Apakah Anda yakin ingin menandai transaksi ini sebagai lunas?') || event.stopImmediatePropagation()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-full">Tandai Lunas</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Tidak ada piutang yang belum lunas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
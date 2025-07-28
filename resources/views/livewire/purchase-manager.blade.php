<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari pembelian..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-1/3">
        <div class="flex items-center">
            <label for="filterStatus" class="mr-2 text-gray-700 text-sm font-bold">Status:</label>
            <x-flux.button.group wire:model.live="filterStatus">
                <x-flux.button.group.option property="filterStatus" value="all" label="Semua" />
                <x-flux.button.group.option property="filterStatus" value="unpaid" label="Belum Lunas" />
                <x-flux.button.group.option property="filterStatus" value="paid" label="Lunas" />
            </x-flux.button.group>
        </div>
        <a href="{{ route('purchases.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Tambah Pembelian</a>
    </div>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <div class="overflow-x-auto"> <!-- Added for responsiveness -->
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pembelian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pembelian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Jatuh Tempo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($purchases as $purchase)
                    <tr class="cursor-pointer hover:bg-gray-100" onclick="window.location='{{ route('purchases.show', $purchase->id) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->purchase_date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($purchase->total_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->due_date ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($purchase->payment_status) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

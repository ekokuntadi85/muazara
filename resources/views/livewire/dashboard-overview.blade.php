<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Dashboard Muazara-App</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1: Jumlah Penjualan Hari Ini -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($salesToday, 2) }}</p>
            </div>
            <div class="text-green-500 text-4xl">
                <i class="fas fa-dollar-sign"></i> <!-- Contoh ikon, ganti dengan ikon Tailwind/Heroicons jika ada -->
            </div>
        </div>

        <!-- Card 2: Jumlah Kunjungan Hari Ini -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Kunjungan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900">{{ $visitsToday }}</p>
            </div>
            <div class="text-blue-500 text-4xl">
                <i class="fas fa-users"></i> <!-- Contoh ikon -->
            </div>
        </div>

        <!-- Card 3: Jumlah Obat Mendekati Expire -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Obat Mendekati Expire (30 Hari)</p>
                <p class="text-2xl font-bold text-gray-900">{{ $expiringProductsCount }}</p>
            </div>
            <div class="text-yellow-500 text-4xl">
                <i class="fas fa-exclamation-triangle"></i> <!-- Contoh ikon -->
            </div>
        </div>
    </div>

    <h2 class="text-2xl font-bold mb-4 mt-10">Daftar 10 Penjualan Terakhir</h2>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($latestTransactions as $transaction)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->customer->name ?? 'Umum' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap currency-cell">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($transaction->total_price, 2) }}</span>
                            </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->payment_status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Tidak ada transaksi terbaru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="text-2xl font-bold mb-4 mt-10">Pembelian Mendekati Jatuh Tempo</h2>

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Invoice</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($upcomingUnpaidPurchases as $purchase)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->invoice_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->supplier->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap currency-cell">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($purchase->total_price, 2) }}</span>
                            </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($purchase->due_date)->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Tidak ada pembelian mendekati jatuh tempo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
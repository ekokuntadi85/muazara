<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-6 dark:text-gray-100">Dashboard Muazara-App</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1: Jumlah Penjualan Hari Ini -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between dark:bg-gray-700 dark:shadow-lg">
            <div>
                <p class="text-gray-500 text-sm dark:text-gray-300">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($salesToday, 2) }}</p>
            </div>
            <div class="text-green-500 text-4xl">
                <x-heroicon-o-currency-dollar class="w-10 h-10" />
            </div>
        </div>

        <!-- Card 2: Jumlah Kunjungan Hari Ini -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between dark:bg-gray-700 dark:shadow-lg">
            <div>
                <p class="text-gray-500 text-sm dark:text-gray-300">Kunjungan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $visitsToday }}</p>
            </div>
            <div class="text-blue-500 text-4xl">
                <x-heroicon-o-users class="w-10 h-10" />
            </div>
        </div>

        <!-- Card 3: Jumlah Obat Mendekati Expire -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between dark:bg-gray-700 dark:shadow-lg">
            <div>
                <p class="text-gray-500 text-sm dark:text-gray-300">Obat Mendekati Expire (30 Hari)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $expiringProductsCount }}</p>
            </div>
            <div class="text-yellow-500 text-4xl">
                <x-heroicon-o-exclamation-triangle class="w-10 h-10" />
            </div>
        </div>
    </div>

    <h2 class="text-2xl font-bold mb-4 mt-10 dark:text-gray-100">Daftar 10 Penjualan Terakhir</h2>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status Pembayaran</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($latestTransactions as $transaction)
                <tr class="dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->customer->name ?? 'Umum' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($transaction->total_price, 2) }}</span>
                            </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->payment_status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada transaksi terbaru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View for Latest Transactions -->
    <div class="block md:hidden space-y-4 mb-8">
        @forelse($latestTransactions as $transaction)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">#{{ $transaction->id }} ({{ $transaction->type }})</span>
                <span class="text-xs text-gray-600 dark:text-gray-300">{{ $transaction->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Customer:</span> {{ $transaction->customer->name ?? 'Umum' }}
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Total:</span> Rp {{ number_format($transaction->total_price, 2) }}
            </div>
            <div class="text-gray-700 dark:text-gray-200">
                <span class="font-medium">Status:</span> {{ $transaction->payment_status }}
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada transaksi terbaru.</p>
        @endforelse
    </div>

    <h2 class="text-2xl font-bold mb-4 mt-10 dark:text-gray-100">Pembelian Mendekati Jatuh Tempo</h2>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nomor Invoice</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Harga</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($upcomingUnpaidPurchases as $purchase)
                <tr class="dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $purchase->invoice_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $purchase->supplier->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($purchase->total_price, 2) }}</span>
                            </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($purchase->due_date)->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada pembelian mendekati jatuh tempo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View for Upcoming Unpaid Purchases -->
    <div class="block md:hidden space-y-4">
        @forelse($upcomingUnpaidPurchases as $purchase)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Invoice: {{ $purchase->invoice_number }}</span>
                <span class="text-xs text-gray-600 dark:text-gray-300">Jatuh Tempo: {{ \Carbon\Carbon::parse($purchase->due_date)->format('Y-m-d') }}</span>
            </div>
            <div class="text-gray-700 dark:text-gray-200 mb-1">
                <span class="font-medium">Supplier:</span> {{ $purchase->supplier->name }}
            </div>
            <div class="text-gray-700 dark:text-gray-200">
                <span class="font-medium">Total:</span> Rp {{ number_format($purchase->total_price, 2) }}
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada pembelian mendekati jatuh tempo.</p>
        @endforelse
    </div>
</div>

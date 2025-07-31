<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <style>
        @media (max-width: 768px) {
            .mobile-card table, .mobile-card thead, .mobile-card tbody, .mobile-card th, .mobile-card td, .mobile-card tr {
                display: block;
            }
            .mobile-card thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .mobile-card tr {
                border: 1px solid #ccc;
                border-radius: 0.5rem;
                margin-bottom: 1rem;
            }
            .mobile-card td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            .mobile-card td:before {
                position: absolute;
                top: 6px;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
            }
        }
    </style>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Laporan Penjualan</h2>

    {{-- Main Content Area --}}
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">

        @if ($viewMode === 'detail')
            <div class="flex justify-start mb-4">
                <button wire:click="showSummary()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-800">
                    &larr; Kembali ke Ringkasan
                </button>
            </div>
        @endif

        {{-- Date Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="startDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Mulai:</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="startDate" wire:model.live="startDate">
            </div>
            <div>
                <label for="endDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Akhir:</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="endDate" wire:model.live="endDate">
            </div>
            <div class="flex items-end">
                <button type="button" wire:click="filter()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">Filter</button>
            </div>
        </div>

        {{-- Totals and Profit/Loss --}}
        <div class="text-left text-xl font-bold mb-4 dark:text-gray-100">
            @if ($viewMode === 'summary')
                Total Omset ({{ $startDate }} - {{ $endDate }}): Rp {{ number_format($totalRevenue, 2) }}
            @else
                Total Omset ({{ $selectedDate }}): Rp {{ number_format($dailyTotalRevenue, 2) }}
            @endif

            @if ($showProfitLossValue)
                <div class="text-left text-xl font-bold mt-3 dark:text-gray-100">
                    Laba/Rugi: Rp {{ number_format($totalProfitLoss, 2) }}
                </div>
            @endif
        </div>

        <div class="flex items-center mt-4">
            <input type="password" class="shadow appearance-none border rounded w-40 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" placeholder="Kode Proteksi" wire:model.defer="profitLossCode">
            <button type="button" wire:click="calculateProfitLoss()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-purple-600 dark:hover:bg-purple-700">
                Lihat Laba/Rugi
            </button>
        </div>
        @error('profitLossCode') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
    </div>

    {{-- Table Area --}}
    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700 mobile-card">
        <div class="overflow-x-auto">
            @if ($viewMode === 'summary')
                {{-- Daily Summary View --}}
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Omset</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($dailySummaries as $summary)
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer" wire:click="viewDailyReport('{{ $summary->date }}')">
                            <td data-label="Tanggal" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($summary->date)->format('d F Y') }}</td>
                            <td data-label="Total Omset" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($summary->daily_total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada data penjualan untuk periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $dailySummaries->links() }}
                </div>
            @else
                {{-- Daily Detail View --}}
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">ID Transaksi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nomor Invoice</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Customer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Harga</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status Pembayaran</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($transactions as $transaction)
                        <tr class="dark:hover:bg-gray-700">
                            <td data-label="ID Transaksi" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->id }}</td>
                            <td data-label="Waktu" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->created_at->format('H:i') }}</td>
                            <td data-label="Nomor Invoice" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->invoice_number ?? '-' }}</td>
                            <td data-label="Customer" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->customer->name ?? 'Umum' }}</td>
                            <td data-label="Total Harga" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($transaction->total_price, 2) }}</td>
                            <td data-label="Status Pembayaran" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->payment_status }}</td>
                            <td data-label="Kasir" class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->user->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada data penjualan untuk tanggal ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

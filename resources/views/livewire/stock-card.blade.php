<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Kartu Stok</h2>

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Filter</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4 relative">
                <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk:</label>
                <input type="text" id="searchProduct" wire:model.live.debounce.300ms="searchProduct" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" placeholder="Cari nama produk atau SKU...">
                
                @if(!empty($productResults))
                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-800 dark:border-gray-600">
                        @foreach($productResults as $product)
                            <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                {{ $product->name }} (SKU: {{ $product->sku }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mb-4">
                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan:</label>
                <select id="month" wire:model.live="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @foreach($months as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
                <select id="year" wire:model.live="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @foreach($years as $yearOption)
                        <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($selectedProductId)
            <div class="mt-6 p-4 border rounded-md bg-gray-50 dark:bg-gray-800">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Produk Terpilih: {{ $selectedProductName }}</p>
                <p class="text-gray-700 dark:text-gray-300">Saldo Awal (s/d {{ \Carbon\Carbon::parse($startDate)->subDay()->format('d M Y') }}): <span class="font-bold">{{ $initialBalance }}</span></p>
                <p class="text-gray-700 dark:text-gray-300">Saldo Akhir (s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}): <span class="font-bold">{{ $finalBalance }}</span></p>
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400 mt-4">Silakan cari dan pilih produk untuk melihat kartu stok.</p>
        @endif
    </div>

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold dark:text-gray-100">Pergerakan Stok</h3>
        <a href="{{ route('reports.stock-card.print', [
            'product_id' => $selectedProductId,
            'start_date' => \Carbon\Carbon::parse($startDate)->format('Y-m-d'),
            'end_date' => \Carbon\Carbon::parse($endDate)->format('Y-m-d'),
        ]) }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">
            Cetak Kartu Stok
        </a>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kuantitas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Catatan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @php
                        $currentBalance = $initialBalance;
                    @endphp
                    @forelse($stockMovements as $movement)
                        @php
                            $currentBalance += $movement->quantity;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($movement->created_at)->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->remarks }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->productBatch->batch_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $currentBalance }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">Tidak ada pergerakan stok dalam periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View for History -->
    <div class="block md:hidden space-y-4">
        @php
            $currentBalance = $initialBalance;
        @endphp
        @forelse($stockMovements as $movement)
            @php
                $currentBalance += $movement->quantity;
            @endphp
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $movement->type }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($movement->created_at)->format('d M Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Kuantitas</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $movement->quantity }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Batch</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $movement->productBatch->batch_number }}</span>
                    </div>
                    @if($movement->remarks)
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Catatan: {{ $movement->remarks }}
                    </div>
                    @endif
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-gray-600 dark:text-gray-400">Saldo Akhir</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $currentBalance }}</span>
                    </div>
                </div>
            </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada pergerakan stok dalam periode ini.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $stockMovements->links() }}
    </div>
</div>
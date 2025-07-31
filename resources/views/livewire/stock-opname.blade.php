<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Stok Opname</h2>

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Pilih Produk</h3>
        <div class="mb-4 relative">
            <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk:</label>
            <input type="text" id="searchProduct" wire:model.live.debounce.300ms="searchProduct" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" placeholder="Cari nama produk atau SKU...">
            
            @if(!empty($productResults))
                <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-800 dark:border-gray-600">
                    @foreach($productResults as $product)
                        <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                            {{ $product->name }} (SKU: {{ $product->sku }}) - Stok Sistem: {{ $product->total_stock ?? 0 }}
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('selectedProductId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        @if($selectedProductId)
            <div class="mt-6 p-4 border rounded-md bg-gray-50 dark:bg-gray-800">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">Produk Terpilih: {{ $selectedProductName }}</p>
                <p class="text-gray-700 dark:text-gray-300">Stok Sistem: <span class="font-bold">{{ $systemStock }}</span></p>
            </div>

            <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Koreksi Stok</h3>
            <div class="mb-4">
                <label for="physicalStock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Fisik:</label>
                <input type="number" id="physicalStock" wire:model.live="physicalStock" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('physicalStock') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="difference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selisih:</label>
                <input type="text" id="difference" value="{{ $physicalStock - $systemStock }}" readonly class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-600">
            </div>
            <div class="mb-6">
                <label for="correctionRemarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Koreksi (Opsional):</label>
                <textarea id="correctionRemarks" wire:model="correctionRemarks" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"></textarea>
                @error('correctionRemarks') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end">
                <button type="button" wire:click="saveAdjustment()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">Simpan Koreksi</button>
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400 mt-4">Silakan cari dan pilih produk untuk memulai stok opname.</p>
        @endif
    </div>

    <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">Riwayat Stok Opname</h3>
    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kuantitas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($stockMovements as $movement)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($movement->created_at)->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->productBatch->product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->productBatch->batch_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $movement->remarks }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">Tidak ada riwayat stok opname.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View for History -->
    <div class="block md:hidden space-y-4">
        @forelse($stockMovements as $movement)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $movement->productBatch->product->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Batch: {{ $movement->productBatch->batch_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Kuantitas</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $movement->quantity }}</p>
                </div>
            </div>
            <div class="mt-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Tanggal</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($movement->created_at)->format('d M Y H:i') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Tipe</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $movement->type }}</span>
                </div>
                @if($movement->remarks)
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Catatan: {{ $movement->remarks }}
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada riwayat stok opname.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $stockMovements->links() }}
    </div>
</div>
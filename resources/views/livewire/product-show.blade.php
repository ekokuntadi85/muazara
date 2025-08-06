<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h2>
                <p class="text-md text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
            </div>
            <div class="flex space-x-2 mt-4 md:mt-0">
                <a href="{{ route('products.edit', $product->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg dark:bg-blue-600 dark:hover:bg-blue-700">Edit</a>
                @can('access-dashboard')
                    
                <button wire:click="deleteProduct()" wire:confirm="Apakah Anda yakin ingin menghapus produk ini?" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 border-b pb-2">Detail Produk</h3>
                <div class="mt-4 space-y-4">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-300">Harga Jual Dasar</span>
                        <span class="text-gray-900 dark:text-white">Rp {{ number_format($product->baseUnit->selling_price, 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-300">Kategori</span>
                        <span class="text-gray-900 dark:text-white">{{ $product->category->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-300">Satuan Dasar</span>
                        <span class="text-gray-900 dark:text-white">{{ $product->baseUnit->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-300">Total Stok</span>
                        <span class="font-bold text-lg text-gray-900 dark:text-white">{{ $product->total_stock }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 border-b pb-2">Detail Satuan</h3>
                <div class="mt-4 space-y-4">
                    @forelse($product->productUnits as $unit)
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $unit->name }} @if($unit->is_base_unit) (Dasar) @endif</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Faktor Konversi: {{ $unit->conversion_factor }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Harga Jual: Rp {{ number_format($unit->selling_price, 0) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Harga Beli: Rp {{ number_format($unit->purchase_price, 0) }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">Tidak ada satuan lain yang ditentukan.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 border-b pb-2">Sejarah Stok</h3>
                <div class="mt-4 space-y-4">
                    @forelse($product->productBatches as $batch)
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm @if($batch->purchase) cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 @endif" @if($batch->purchase) onclick="window.location='{{ route('purchases.show', $batch->purchase->id) }}'" @endif>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $batch->purchase->supplier->name ?? 'Stok Awal' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $batch->purchase->purchase_date ?? $batch->created_at->format('Y-m-d') }}</p>
                                    @if ($batch->expiration_date)
                                        @php
                                            $expires = \Carbon\Carbon::parse($batch->expiration_date);
                                            $isExpired = $expires->isPast();
                                            $isExpiringSoon = !$isExpired && $expires->isBefore(now()->addDays(90));
                                            
                                            $statusClass = '';
                                            if ($isExpired) {
                                                $statusClass = 'text-red-500 dark:text-red-400 font-semibold';
                                            } elseif ($isExpiringSoon) {
                                                $statusClass = 'text-yellow-500 dark:text-yellow-400 font-semibold';
                                            }
                                        @endphp
                                        <p class="text-sm {{ $statusClass }}">
                                            ED: {{ $expires->format('d/m/Y') }}
                                            @if($isExpired)
                                                (Expired)
                                            @elseif($isExpiringSoon)
                                                (Expires Soon)
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-gray-800 dark:text-gray-100">{{ $batch->stock }} <span class="text-sm font-normal">{{ $product->baseUnit->name }}</span></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">@ Rp {{ number_format($batch->purchase_price, 0) }}</p>
                                    
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">Tidak ada sejarah stok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
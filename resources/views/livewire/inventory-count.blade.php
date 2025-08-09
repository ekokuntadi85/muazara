<div>
    <div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">

        @if(session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if($view === 'list')
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Riwayat Stok Opname</h1>
                <button type="button" wire:click="changeView('create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:hover:bg-gray-500">Buat Opname Baru</button>
            </div>
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Catatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Petugas</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($opnames as $opname)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $opname->opname_date }}</td>
                                <td class="px-6 py-4">{{ $opname->notes }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $opname->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button type="button" wire:click="changeView('detail', {{ $opname->id }})" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">Lihat</button>
                                    <button type="button" wire:click="deleteOpname({{ $opname->id }})" wire:confirm="Apakah Anda yakin ingin menghapus opname ini? Stok akan dikembalikan ke keadaan semula." class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition ease-in-out duration-150">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">Tidak ada riwayat opname.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $opnames->links() }}</div>

        @elseif($view === 'create')
            <h1 class="text-2xl font-bold mb-4">Buat Stok Opname Baru</h1>
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
                <div class="mb-4">
                    <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cari Produk</label>
                    <input type="text" id="searchProduct" wire:model.live.debounce.300ms="searchProduct" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600" placeholder="Ketik untuk mencari produk...">
                    @if(!empty($productResults))
                        <ul class="border border-gray-300 rounded-md mt-1 bg-white dark:bg-gray-800">
                            @foreach($productResults as $product)
                                <li class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer" wire:click="addProductToOpname({{ $product->id }})">{{ $product->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <form wire:submit.prevent="saveOpname">
                    <div class="mb-4">
                        <label for="opname_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Catatan (Opsional)</label>
                        <textarea id="opname_notes" wire:model="opname_notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600"></textarea>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 mb-4">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Batch</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stok Sistem</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stok Fisik</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse($opname_items as $index => $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['product_name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['batch_number'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item['system_stock'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <input type="number" wire:model="opname_items.{{ $index }}.physical_stock" class="w-24 text-center rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-8 text-gray-500">Tambahkan produk untuk diopname dengan menggunakan pencarian di atas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('opname_items') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror

                    <div class="flex justify-end space-x-4 mt-4">
                        <button type="button" wire:click="changeView('list')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">Batal</button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:hover:bg-gray-500">Simpan Opname</button>
                    </div>
                </form>
            </div>

        @elseif($view === 'detail')
            <h1 class="text-2xl font-bold mb-4">Detail Stok Opname #{{ $selectedOpname->id }}</h1>
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
                <p><strong>Tanggal:</strong> {{ $selectedOpname->opname_date }}</p>
                <p><strong>Petugas:</strong> {{ $selectedOpname->user->name }}</p>
                <p><strong>Catatan:</strong> {{ $selectedOpname->notes }}</p>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 mt-4 table-fixed">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-2/5">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Batch</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Stok Sistem</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Stok Fisik</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($selectedOpname->details as $detail)
                            <tr>
                                <td class="px-6 py-4">{{ $detail->productBatch->product->name }}</td>
                                <td class="px-6 py-4">{{ $detail->productBatch->batch_number }}</td>
                                <td class="px-6 py-4 text-center">{{ $detail->system_stock }}</td>
                                <td class="px-6 py-4 text-center">{{ $detail->physical_stock }}</td>
                                <td class="px-6 py-4 text-center">{{ $detail->difference }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="changeView('list')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">Kembali</button>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
        <div class="flex flex-col md:flex-row justify-between md:items-start mb-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Pembelian #{{ $purchase->invoice_number }}</h2>
                <p class="text-md text-gray-500 dark:text-gray-400">Dari: {{ $purchase->supplier->name }}</p>
            </div>
            <div class="mt-4 md:mt-0 text-right">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total: Rp {{ number_format($purchase->total_price, 0) }}</p>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                    {{ $purchase->payment_status == 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' }}">
                    {{ ucfirst($purchase->payment_status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 border-t border-b py-2 border-gray-200 dark:border-gray-600">
            <div>
                <h4 class="font-semibold text-gray-600 dark:text-gray-300">Tanggal Pembelian</h4>
                <p class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-600 dark:text-gray-300">Nomor Invoice</h4>
                <p class="text-gray-900 dark:text-white">{{ $purchase->invoice_number }}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-600 dark:text-gray-300">Jatuh Tempo</h4>
                <p class="text-gray-900 dark:text-white">{{ $purchase->due_date ? \Carbon\Carbon::parse($purchase->due_date)->format('d/m/Y') : '-' }}</p>
            </div>
        </div>

        <div class="mt-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Item Pembelian</h3>
            <div class="space-y-4">
                @forelse($purchase->productBatches as $item)
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                            <span class="text-sm text-gray-600 dark:text-gray-400">( {{ $item->batch_number ?: '-' }} )</span>
                        </div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Expire: {{ \Carbon\Carbon::parse($item->expiration_date)->format('m/Y') }}</span>
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $item->stock }} {{ $item->productUnit->name ?? $item->product->baseUnit->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Harga Beli: Rp {{ number_format($item->purchase_price, 0) }}</span>
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Subtotal: Rp {{ number_format($item->purchase_price * $item->stock, 0) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada item dalam pembelian ini.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600 flex flex-col-reverse md:flex-row md:justify-between md:items-center">
            <div class="flex space-x-2 mt-4 md:mt-0">
                <a href="{{ route('purchases.edit', $purchase->id) }}" class="w-full md:w-auto text-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500">Edit</a>
                <button wire:click="deletePurchase()" wire:confirm="Yakin hapus pembelian ini?" class="w-full md:w-auto text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">Hapus</button>
            </div>
            @if ($purchase->payment_status === 'unpaid')
                <button wire:click="markAsPaid()" wire:confirm="Tandai lunas?" class="w-full md:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600">Tandai Lunas</button>
            @endif
        </div>
    </div>
</div>

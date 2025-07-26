<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Detail Customer: {{ $customer->name }}</h2>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Nama Customer:</p>
            <p class="text-gray-900">{{ $customer->name }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Telepon:</p>
            <p class="text-gray-900">{{ $customer->phone ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold">Alamat:</p>
            <p class="text-gray-900">{{ $customer->address ?? '-' }}</p>
        </div>

        <div class="flex justify-end mt-4">
            <a href="{{ route('customers.edit', $customer->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2">Edit Customer</a>
            <button wire:click="deleteCustomer()" onclick="confirm('Apakah Anda yakin ingin menghapus customer ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full">Hapus Customer</button>
        </div>
    </div>
</div>
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Edit Customer</h2>

    <form wire:submit.prevent="save" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <input type="hidden" wire:model="customerId">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Customer:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" placeholder="Masukkan Nama Customer" wire:model="name">
            @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Telepon:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" placeholder="Masukkan Nomor Telepon" wire:model="phone">
            @error('phone') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-6">
            <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Alamat:</label>
            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" placeholder="Masukkan Alamat" wire:model="address"></textarea>
            @error('address') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update
            </button>
            <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Batal</a>
        </div>
    </form>
</div>
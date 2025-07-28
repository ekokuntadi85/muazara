<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Edit Customer</h2>

    <form wire:submit.prevent="save" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <input type="hidden" wire:model="customerId">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Nama Customer:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="name" placeholder="Masukkan Nama Customer" wire:model="name">
            @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Telepon:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="phone" placeholder="Masukkan Nomor Telepon" wire:model="phone">
            @error('phone') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-6">
            <label for="address" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Alamat:</label>
            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="address" placeholder="Masukkan Alamat" wire:model="address"></textarea>
            @error('address') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">
                Update
            </button>
            <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-700">Batal</a>
        </div>
    </form>
</div>
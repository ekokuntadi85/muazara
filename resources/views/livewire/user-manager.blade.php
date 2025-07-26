<div class="container mx-auto p-4">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4">Manajemen Pengguna</h2>

    <form wire:submit.prevent="save" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <input type="hidden" wire:model="userId">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" wire:model="name">
            @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" wire:model="email">
            @error('email') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
            <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" wire:model="password">
            @error('password') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            @if($isUpdateMode) <p class="text-gray-600 text-xs mt-1">Kosongkan jika tidak ingin mengubah password.</p> @endif
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">{{ $isUpdateMode ? 'Update' : 'Tambah' }}</button>
            <button type="button" wire:click="resetInput()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Batal</button>
        </div>
    </form>

    <hr class="my-8">

    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="edit({{ $user->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full mr-2">Edit</button>
                            <button wire:click="delete({{ $user->id }})" onclick="confirm('Apakah Anda yakin ingin menghapus pengguna ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-800 dark:border-red-700 dark:text-red-200" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col-reverse md:flex-row md:justify-between md:items-center mb-4 space-y-4 md:space-y-0">
        <div class="relative w-full md:w-1/3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kategori..." class="shadow appearance-none border rounded py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        </div>
        <button type="button" wire:click="createCategory()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700 md:ml-4">Tambah Kategori</button>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Deskripsi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($categories as $category)
                    <tr class="dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $category->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $category->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="edit({{ $category->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full mr-2 dark:bg-green-600 dark:hover:bg-green-700">Edit</button>
                            <button wire:click="delete({{ $category->id }})" onclick="confirm('Apakah Anda yakin ingin menghapus kategori ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($categories as $category)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $category->description }}</p>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="edit({{ $category->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-green-600 dark:hover:bg-green-700">Edit</button>
                    <button wire:click="delete({{ $category->id }})" onclick="confirm('Apakah Anda yakin ingin menghapus kategori ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
                </div>
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada kategori ditemukan.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-show="$wire.showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" @click.away="$wire.closeModal()">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">{{ $isUpdateMode ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</h3>
            <form wire:submit.prevent="save">
                <input type="hidden" wire:model="categoryId">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Nama Kategori:</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" id="name" placeholder="Masukkan Nama Kategori" wire:model="name">
                    @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
                </div>
                <div class="mb-6">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Deskripsi:</label>
                    <textarea class="form-control shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" id="description" placeholder="Masukkan Deskripsi" wire:model="description"></textarea>
                    @error('description') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
                </div>
                <div class="flex items-center justify-end space-x-2">
                    <button type="button" wire:click="closeModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-700">Batal</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">{{ $isUpdateMode ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
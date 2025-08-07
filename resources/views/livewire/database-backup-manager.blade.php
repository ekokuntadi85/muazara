<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Backup Database</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola backup database Anda di sini.</p>
        </div>

        <div class="p-6">
            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="mb-6">
                <button wire:click="performBackup" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <span wire:loading.remove wire:target="performBackup">Buat Backup Baru</span>
                    <span wire:loading wire:target="performBackup">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Membuat Backup...
                    </span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Dimodifikasi</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($backups as $backup)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $backup['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($backup['size'] / 1024 / 1024, 2) }} MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->format('d M Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="downloadBackup('{{ $backup['name'] }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Download</button>
                                    <button wire:click="deleteBackup('{{ $backup['name'] }}')" onclick="confirm('Apakah Anda yakin ingin menghapus backup ini?') || event.stopImmediatePropagation()" class="text-red-600 hover:text-red-900">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Tidak ada backup yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

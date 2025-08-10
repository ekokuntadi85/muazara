
<div>
    <div class="mb-4">
        <h2 class="text-2xl font-bold">Restore Database</h2>
        <p class="text-gray-600">Pilih file backup SQL (.sql) untuk me-restore database. Semua data yang ada saat ini akan ditimpa.</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form wire:submit.prevent="restoreDatabase">
            <div class="mb-4">
                <label for="sqlFile" class="block text-sm font-medium text-gray-700">File Backup SQL</label>
                <input type="file" id="sqlFile" wire:model="sqlFile" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                @error('sqlFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" wire:loading.attr="disabled" wire:target="restoreDatabase">
                    <span wire:loading.remove wire:target="restoreDatabase">Restore Database</span>
                    <span wire:loading wire:target="restoreDatabase">Me-restore...</span>
                </button>
            </div>
        </form>

        @if($isRestoring || !empty($restoreLog))
            <div class="mt-6 p-4 bg-gray-900 text-white font-mono text-sm rounded-lg overflow-x-auto">
                <h4 class="font-bold mb-2">Log Proses Restore:</h4>
                <pre class="whitespace-pre-wrap">{{ $restoreLog }}</pre>
            </div>
        @endif
    </div>
</div>

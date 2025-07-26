<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Edit Produk</h2>

    <form wire:submit.prevent="save" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <input type="hidden" wire:model="productId">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Produk:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" placeholder="Masukkan Nama Produk" wire:model="name">
            @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">SKU:</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sku" placeholder="Masukkan SKU Produk" wire:model="sku">
            @error('sku') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="selling_price" class="block text-gray-700 text-sm font-bold mb-2">Harga Jual:</label>
            <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="selling_price" placeholder="Masukkan Harga Jual" wire:model="selling_price">
            @error('selling_price') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Kategori:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="category_id" wire:model="category_id">
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="mb-6">
            <label for="unit_id" class="block text-gray-700 text-sm font-bold mb-2">Satuan:</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="unit_id" wire:model="unit_id">
                <option value="">Pilih Satuan</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
            @error('unit_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update
            </button>
            <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Batal</a>
        </div>
    </form>
</div>
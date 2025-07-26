<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Livewire\Component;

class ProductCreate extends Component
{
    public $name;
    public $sku;
    public $selling_price;
    public $category_id;
    public $unit_id;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'selling_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama produk wajib diisi.',
        'sku.required' => 'SKU produk wajib diisi.',
        'sku.unique' => 'SKU produk sudah ada.',
        'selling_price.required' => 'Harga jual wajib diisi.',
        'selling_price.numeric' => 'Harga jual harus berupa angka.',
        'selling_price.min' => 'Harga jual tidak boleh negatif.',
        'category_id.required' => 'Kategori wajib dipilih.',
        'category_id.exists' => 'Kategori tidak valid.',
        'unit_id.required' => 'Satuan wajib dipilih.',
        'unit_id.exists' => 'Satuan tidak valid.',
    ];

    public function save()
    {
        $this->validate();

        Product::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'selling_price' => $this->selling_price,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
        ]);

        session()->flash('message', 'Produk berhasil ditambahkan.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        $categories = Category::all();
        $units = Unit::all();
        return view('livewire.product-create', compact('categories', 'units'));
    }
}
<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Livewire\Component;
use Illuminate\Validation\Rule;

class ProductManager extends Component
{
    public $name;
    public $sku;
    public $selling_price;
    public $category_id;
    public $unit_id;
    public $productId;
    public $isUpdateMode = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
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

    public function render()
    {
        $products = Product::with(['category', 'unit'])->latest()->get();
        $categories = Category::all();
        $units = Unit::all();
        return view('livewire.product-manager', compact('products', 'categories', 'units'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $product = Product::find($this->productId);
            $product->update([
                'name' => $this->name,
                'sku' => $this->sku,
                'selling_price' => $this->selling_price,
                'category_id' => $this->category_id,
                'unit_id' => $this->unit_id,
            ]);
            session()->flash('message', 'Produk berhasil diperbarui.');
        } else {
            Product::create([
                'name' => $this->name,
                'sku' => $this->sku,
                'selling_price' => $this->selling_price,
                'category_id' => $this->category_id,
                'unit_id' => $this->unit_id,
            ]);
            session()->flash('message', 'Produk berhasil ditambahkan.');
        }

        $this->resetInput();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->selling_price = $product->selling_price;
        $this->category_id = $product->category_id;
        $this->unit_id = $product->unit_id;
        $this->isUpdateMode = true;
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('message', 'Produk berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->sku = '';
        $this->selling_price = '';
        $this->category_id = '';
        $this->unit_id = '';
        $this->productId = null;
        $this->isUpdateMode = false;
    }
}
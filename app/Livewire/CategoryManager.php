<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    public $name;
    public $description;
    public $categoryId;
    public $isUpdateMode = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->categoryId),
            ],
            'description' => 'nullable|string',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi.',
        'name.unique' => 'Nama kategori sudah ada.',
    ];

    public function render()
    {
        $categories = Category::latest()->paginate(5);
        return view('livewire.category-manager', compact('categories'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $category = Category::find($this->categoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Kategori berhasil diperbarui.');
        } else {
            Category::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Kategori berhasil ditambahkan.');
        }

        $this->resetInput();
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isUpdateMode = true;
    }

    public function delete($id)
    {
        Category::find($id)->delete();
        session()->flash('message', 'Kategori berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->description = '';
        $this->categoryId = null;
        $this->isUpdateMode = false;
    }
}
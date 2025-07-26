<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Illuminate\Validation\Rule;

class SupplierManager extends Component
{
    public $name;
    public $phone;
    public $address;
    public $supplierId;
    public $isUpdateMode = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'name')->ignore($this->supplierId),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama supplier wajib diisi.',
        'name.unique' => 'Nama supplier sudah ada.',
    ];

    public function render()
    {
        $suppliers = Supplier::latest()->get();
        return view('livewire.supplier-manager', compact('suppliers'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $supplier = Supplier::find($this->supplierId);
            $supplier->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Supplier berhasil ditambahkan.');
        }

        $this->resetInput();
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->isUpdateMode = true;
    }

    public function delete($id)
    {
        Supplier::find($id)->delete();
        session()->flash('message', 'Supplier berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->phone = '';
        $this->address = '';
        $this->supplierId = null;
        $this->isUpdateMode = false;
    }
}
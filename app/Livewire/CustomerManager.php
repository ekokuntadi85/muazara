<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CustomerManager extends Component
{
    public $name;
    public $phone;
    public $address;
    public $customerId;
    public $isUpdateMode = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name')->ignore($this->customerId),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama customer wajib diisi.',
        'name.unique' => 'Nama customer sudah ada.',
    ];

    public function render()
    {
        $customers = Customer::latest()->get();
        return view('livewire.customer-manager', compact('customers'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $customer = Customer::find($this->customerId);
            $customer->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Customer berhasil diperbarui.');
        } else {
            Customer::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Customer berhasil ditambahkan.');
        }

        $this->resetInput();
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->isUpdateMode = true;
    }

    public function delete($id)
    {
        Customer::find($id)->delete();
        session()->flash('message', 'Customer berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->phone = '';
        $this->address = '';
        $this->customerId = null;
        $this->isUpdateMode = false;
    }
}
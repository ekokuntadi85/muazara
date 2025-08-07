<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\Validation\Rule;

use Livewire\Attributes\Title;

#[Title('Edit Pelanggan')]
class CustomerEdit extends Component
{
    public $customerId;
    public $name;
    public $phone;
    public $address;

    public function mount(Customer $customer)
    {
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers', 'phone')->ignore($this->customerId),
            ],
            'address' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama customer wajib diisi.',
        'phone.unique' => 'Nomor telepon sudah terdaftar.',
    ];

    public function save()
    {
        $this->validate();

        $customer = Customer::find($this->customerId);
        $customer->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        session()->flash('message', 'Customer berhasil diperbarui.');

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customer-edit');
    }
}

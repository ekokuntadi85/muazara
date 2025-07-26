<?php

namespace App\Livewire;

use App\Models\Unit;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

class UnitManager extends Component
{
    use WithPagination;

    public $name;
    public $short_name;
    public $unitId;
    public $isUpdateMode = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($this->unitId),
            ],
            'short_name' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama unit wajib diisi.',
        'name.unique' => 'Nama unit sudah ada.',
    ];

    public function render()
    {
        $units = Unit::latest()->paginate(5);
        return view('livewire.unit-manager', compact('units'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $unit = Unit::find($this->unitId);
            $unit->update([
                'name' => $this->name,
                'short_name' => $this->short_name,
            ]);
            session()->flash('message', 'Unit berhasil diperbarui.');
        } else {
            Unit::create([
                'name' => $this->name,
                'short_name' => $this->short_name,
            ]);
            session()->flash('message', 'Unit berhasil ditambahkan.');
        }

        $this->resetInput();
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->short_name = $unit->short_name;
        $this->isUpdateMode = true;
    }

    public function delete($id)
    {
        Unit::find($id)->delete();
        session()->flash('message', 'Unit berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->short_name = '';
        $this->unitId = null;
        $this->isUpdateMode = false;
    }
}

<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserManager extends Component
{
    use WithPagination;

    public $name;
    public $email;
    public $password;
    public $userId;
    public $isUpdateMode = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $this->isUpdateMode ? 'nullable|string|min:8' : 'required|string|min:8',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 8 karakter.',
    ];

    public function render()
    {
        $users = User::latest()->paginate(5);
        return view('livewire.user-manager', compact('users'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $user = User::find($this->userId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);
            session()->flash('message', 'Pengguna berhasil diperbarui.');
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            session()->flash('message', 'Pengguna berhasil ditambahkan.');
        }

        $this->resetInput();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->isUpdateMode = true;
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'Pengguna berhasil dihapus.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->userId = null;
        $this->isUpdateMode = false;
    }
}
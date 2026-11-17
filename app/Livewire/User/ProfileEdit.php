<?php

namespace App\Livewire\User;

use Livewire\Component;

class ProfileEdit extends Component
{
    public $name, $email, $phone;

    public function mount()
    {
        $u = auth()->user();
        $this->name = $u->name;
        $this->email = $u->email;
        $this->phone = $u->phone;
    }

    protected $rules = ['name' => 'required|min:2', 'email' => 'required|email', 'phone' => 'nullable|string|max:30'];

    public function save()
    {
        $this->validate();
        auth()->user()->update(['name' => $this->name, 'email' => $this->email, 'phone' => $this->phone]);
        session()->flash('message', 'Profile updated.');
    }

    public function render()
    {
        return view('livewire.user.profile-edit');
    }
}

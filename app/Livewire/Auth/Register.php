<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.app')]
class Register extends Component
{
    public $name = '', $email = '', $password = '', $password_confirmation = '';
    public $user_type = 'parent';

    protected $rules = ['name' => 'required|min:2', 'email' => 'required|email|unique:users,email', 'password' => 'required|min:8|confirmed'];

    public function register()
    {
        $this->validate();
        $user = User::create(['name' => $this->name, 'email' => $this->email, 'password' => Hash::make($this->password), 'user_type' => $this->user_type, 'status' => 'active', 'email_verified_at' => now()]);
        auth()->login($user);
        return redirect()->route('user.dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
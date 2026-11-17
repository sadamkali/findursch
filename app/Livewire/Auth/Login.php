<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    public $email = '', $password = '', $remember = false;

    protected $rules = ['email' => 'required|email', 'password' => 'required'];

    public function authenticate()
    {
        $this->validate();

        if (auth()->attempt(['email' => $this->email, 'password' => $this->password, 'status' => 'active'], $this->remember)) {
            request()->session()->regenerate();
            $u = auth()->user();
            return redirect()->intended($u->user_type === 'admin' ? '/admin' : ($u->user_type === 'school_owner' ? '/owner' : '/account'));
        }

        $this->addError('email', 'Invalid credentials.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
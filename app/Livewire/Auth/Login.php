<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function authenticate()
    {
        $this->validate();

        $key = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Too many login attempts. Try again in {$seconds} seconds.");

            return;
        }

        RateLimiter::hit($key, 60);

        if (! auth()->attempt([
            'email' => $this->email,
            'password' => $this->password,
            'status' => 'active',
        ], $this->remember)) {
            $this->addError('email', 'The supplied credentials could not be verified.');

            return;
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect('/admin');
        }

        if ($user->hasRole('school_owner')) {
            return redirect()->intended(route('owner.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $account_type = 'parent';

    protected array $rules = [
        'name' => 'required|string|min:2|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'account_type' => 'required|in:parent,school_owner',
    ];

    public function register()
    {
        $key = 'register:'.sha1((string) request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Too many registration attempts. Try again in {$seconds} seconds.");

            return;
        }

        RateLimiter::hit($key, 600);
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'password' => $validated['password'],
        ]);

        // Account type is deliberately assigned by trusted server-side code.
        $user->forceFill([
            'user_type' => $validated['account_type'],
            'status' => 'active',
        ])->save();

        Role::findOrCreate($validated['account_type'], 'web');
        $user->assignRole($validated['account_type']);

        auth()->login($user);
        request()->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}

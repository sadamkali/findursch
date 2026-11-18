<?php

namespace App\Livewire\User;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.account')]
class ProfileEdit extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = (string) ($user->phone ?? '');
    }

    public function save()
    {
        $user = auth()->user();
        $originalEmail = $user->email;

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $changes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
        ];

        // Verification is not required to use FindurSch. A changed address
        // simply clears its historical verification timestamp without blocking the account.
        if ($originalEmail !== $validated['email']) {
            $changes['email_verified_at'] = null;
        }

        $user->forceFill($changes)->save();

        session()->flash('message', 'Your profile has been updated.');
    }

    public function render()
    {
        return view('livewire.user.profile-edit');
    }
}

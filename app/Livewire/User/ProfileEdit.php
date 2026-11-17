<?php

namespace App\Livewire\User;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
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

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
        ])->save();

        if ($originalEmail !== $validated['email']) {
            $user->forceFill(['email_verified_at' => null])->save();
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('status', 'Your email changed. Verify the new address to continue using your account.');
        }

        session()->flash('message', 'Your profile has been updated.');
    }

    public function render()
    {
        return view('livewire.user.profile-edit');
    }
}

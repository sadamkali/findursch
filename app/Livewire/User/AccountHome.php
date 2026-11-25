<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.account')]
class AccountHome extends Component
{
    public function render()
    {
        $user = auth()->user();
        $saved = $user->savedSchools()->with(['district', 'type', 'curricula', 'media'])->limit(5)->get();
        $inquiries = $user->inquiries()->with('school')->latest()->limit(5)->get();
        $reviews = $user->reviews()->with('school')->latest()->limit(5)->get();

        return view('livewire.user.account-home', compact('saved', 'inquiries', 'reviews'));
    }
}
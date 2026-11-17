<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $saved = $user->savedSchools()->with('district', 'type')->limit(5)->get();
        $inquiries = $user->inquiries()->with('school')->latest()->limit(5)->get();
        $reviews = $user->reviews()->with('school')->latest()->limit(5)->get();

        return view('livewire.user.dashboard', compact('saved', 'inquiries', 'reviews'));
    }
}
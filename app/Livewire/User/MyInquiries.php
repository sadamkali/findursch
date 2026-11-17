<?php

namespace App\Livewire\User;

use Livewire\Component;

class MyInquiries extends Component
{
    public function render()
    {
        $inquiries = auth()->user()->inquiries()->with('school')->latest()->paginate(15);
        return view('livewire.user.my-inquiries', compact('inquiries'));
    }
}

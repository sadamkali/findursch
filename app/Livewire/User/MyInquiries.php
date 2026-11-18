<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.account')]
class MyInquiries extends Component
{
    use WithPagination;

    public function render()
    {
        $inquiries = auth()->user()
            ->inquiries()
            ->with('school')
            ->latest()
            ->paginate(15);

        return view('livewire.user.my-inquiries', compact('inquiries'));
    }
}

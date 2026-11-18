<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.account')]
class MyReviews extends Component
{
    use WithPagination;

    public function render()
    {
        $reviews = auth()->user()
            ->reviews()
            ->with('school')
            ->latest()
            ->paginate(15);

        return view('livewire.user.my-reviews', compact('reviews'));
    }
}

<?php

namespace App\Livewire\User;

use Livewire\Component;

class MyReviews extends Component
{
    public function render()
    {
        $reviews = auth()->user()->reviews()->with('school')->latest()->paginate(15);
        return view('livewire.user.my-reviews', compact('reviews'));
    }
}

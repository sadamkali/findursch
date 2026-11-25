<?php

namespace App\\Livewire\\User;

use Livewire\\Attributes\\Layout;
use Livewire\\Component;

#[Layout('layouts.account')]
class AccountHome extends Component
{
    public function render()
    {
        $user = auth()->user();

        $savedCount = $user->savedSchools()->count();
        $inquiryCount = $user->inquiries()->count();
        $reviewCount = $user->reviews()->count();

        $saved = $user->savedSchools()
            ->with(['district', 'type', 'curricula', 'media'])
            ->latest('saved_schools.created_at')
            ->limit(6)
            ->get();

        $inquiries = $user->inquiries()
            ->with('school')
            ->latest()
            ->limit(5)
            ->get();

        $reviews = $user->reviews()
            ->with('school')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.user.account-home', compact(
            'saved',
            'inquiries',
            'reviews',
            'savedCount',
            'inquiryCount',
            'reviewCount'
        ));
    }
}

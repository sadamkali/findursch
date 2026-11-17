<?php

namespace App\Livewire\Owner;

use App\Models\School;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $schools = School::query()
            ->where('owner_id', auth()->id())
            ->with(['district', 'type'])
            ->withCount(['programs', 'inquiries'])
            ->latest()
            ->get();

        return view('livewire.owner.dashboard', [
            'schools' => $schools,
            'totalSchools' => $schools->count(),
            'pendingSchools' => $schools->where('status', 'pending')->count(),
            'activeSchools' => $schools->where('status', 'active')->count(),
            'draftSchools' => $schools->where('status', 'draft')->count(),
        ]);
    }
}

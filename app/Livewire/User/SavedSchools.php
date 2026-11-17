<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SavedSchools extends Component
{
    use WithPagination;

    public function unsave(int $schoolId): void
    {
        auth()->user()->savedSchools()->detach($schoolId);
        session()->flash('message', 'School removed from your saved list.');
    }

    public function render()
    {
        $schools = auth()->user()
            ->savedSchools()
            ->with('district', 'type', 'curricula')
            ->paginate(12);

        return view('livewire.user.saved-schools', compact('schools'));
    }
}

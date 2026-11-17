<?php

namespace App\Livewire\User;

use Livewire\Component;

class SavedSchools extends Component
{
    public function unsave($schoolId)
    {
        auth()->user()->savedSchools()->detach($schoolId);
        session()->flash('message', 'School removed from your saved list.');
    }

    public function render()
    {
        $schools = auth()->user()->savedSchools()->with('district', 'type', 'curricula')->paginate(12);
        return view('livewire.user.saved-schools', compact('schools'));
    }
}

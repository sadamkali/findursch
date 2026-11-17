<?php

namespace App\Livewire\Compare;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompareSchools extends Component
{
    public $schoolIds = [];

    public function mount()
    {
        $this->schoolIds = session('compare_schools', []);
        if (empty($this->schoolIds) && request('schools')) {
            $this->schoolIds = array_slice((array) request('schools'), 0, 3);
            session(['compare_schools' => $this->schoolIds]);
        }
    }

    public function remove($id)
    {
        $this->schoolIds = array_values(array_diff($this->schoolIds, [$id]));
        session(['compare_schools' => $this->schoolIds]);
    }

    public function clear()
    {
        $this->schoolIds = [];
        session()->forget('compare_schools');
    }

    public function render()
    {
        $schools = \App\Models\School::with(['type', 'district', 'curricula', 'facilities'])->whereIn('id', $this->schoolIds)->get();
        return view('livewire.compare.compare-schools', compact('schools'));
    }
}

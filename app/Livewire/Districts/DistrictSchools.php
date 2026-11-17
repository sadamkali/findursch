<?php

namespace App\Livewire\Districts;

use App\Models\District;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DistrictSchools extends Component
{
    use WithPagination;

    public District $district;

    public function mount(District $district): void
    {
        $this->district = $district->load('region');
    }

    public function render()
    {
        $schools = $this->district->schools()
            ->active()
            ->verified()
            ->with(['type', 'district', 'curricula'])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.districts.district-schools', compact('schools'));
    }
}

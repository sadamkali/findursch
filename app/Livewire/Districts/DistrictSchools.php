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

    public $district;

    public function mount(District $district)
    {
        $this->district = $district;
    }

    public function render()
    {
        $schools = $this->district->schools()
            ->active()->verified()
            ->with(['type', 'curricula'])
            ->paginate(15);

        $district = $this->district->load('region');
        return view('livewire.districts.district-schools', compact('schools', 'district'));
    }
}
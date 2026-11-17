<?php

namespace App\Livewire\Schools;

use App\Models\Curriculum;
use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SchoolListing extends Component
{
    use WithPagination;

    #[Url]
    public $district = '';
    #[Url]
    public $type = '';
    #[Url]
    public $curriculum = '';
    #[Url]
    public $boarding = '';
    #[Url]
    public $verified_only = false;
    #[Url]
    public $sort = 'featured';
    #[Url]
    public $search = '';

    public function render()
    {
        $query = School::active()->verified()
            ->with(['district', 'type', 'curricula']);

        if ($this->district) $query->inDistrict((int) $this->district);
        if ($this->type) $query->byType((int) $this->type);
        if ($this->curriculum) $query->byCurriculum((int) $this->curriculum);
        if ($this->boarding) $query->where('boarding_type', $this->boarding);
        if ($this->verified_only) $query->verified();
        if ($this->search) $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('tagline', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%");
        });

        match ($this->sort) {
            'rating' => $query->orderByDesc('rating_average'),
            'newest' => $query->latest('verified_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('is_featured')->latest('verified_at'),
        };

        return view('livewire.schools.school-listing', [
            'schools' => $query->paginate(15),
            'districts' => District::orderBy('name')->get(),
            'schoolTypes' => SchoolType::where('is_active', true)->orderBy('order')->get(),
            'curricula' => Curriculum::where('is_active', true)->get(),
        ]);
    }
}

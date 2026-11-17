<?php

namespace App\Livewire\Search;

use App\Models\School;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SearchResults extends Component
{
    use WithPagination;

    #[Url]
    public $q = '';

    public function render()
    {
        $schools = collect();
        $districts = collect();

        if (strlen($this->q) >= 2) {
            $schools = School::publiclyVisible()
                ->where(function ($query) {
                    $query->where('name', 'like', "%{$this->q}%")
                          ->orWhere('tagline', 'like', "%{$this->q}%")
                          ->orWhere('description', 'like', "%{$this->q}%")
                          ->orWhere('address', 'like', "%{$this->q}%");
                })
                ->with(['district', 'type', 'curricula'])
                ->paginate(15);

            $districts = \App\Models\District::where('name', 'like', "%{$this->q}%")->limit(6)->get();
        }

        return view('livewire.search.search-results', compact('schools', 'districts'));
    }
}

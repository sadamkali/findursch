<?php

namespace App\Livewire\Compare;

use App\Models\School;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompareSchools extends Component
{
    public array $schoolIds = [];

    public function mount(): void
    {
        $requestedSlugs = collect(explode(',', (string) request('schools', '')))
            ->filter()
            ->unique()
            ->take(3)
            ->values();

        if ($requestedSlugs->isNotEmpty()) {
            $this->schoolIds = School::query()
                ->publiclyVisible()
                ->whereIn('slug', $requestedSlugs)
                ->pluck('id')
                ->all();

            session(['compare_schools' => $this->schoolIds]);

            return;
        }

        $this->schoolIds = collect(session('compare_schools', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    public function remove(int $id): void
    {
        $this->schoolIds = array_values(array_diff($this->schoolIds, [$id]));
        session(['compare_schools' => $this->schoolIds]);
    }

    public function clear(): void
    {
        $this->schoolIds = [];
        session()->forget('compare_schools');
    }

    public function render()
    {
        $schools = School::query()
            ->publiclyVisible()
            ->with(['type', 'district', 'curricula', 'facilities'])
            ->whereIn('id', $this->schoolIds)
            ->get();

        $this->schoolIds = $schools->pluck('id')->all();
        session(['compare_schools' => $this->schoolIds]);

        return view('livewire.compare.compare-schools', compact('schools'));
    }
}

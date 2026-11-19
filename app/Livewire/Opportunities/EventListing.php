<?php

namespace App\Livewire\Opportunities;

use App\Models\SchoolPost;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EventListing extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'upcoming')]
    public string $period = 'upcoming';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedPeriod(): void { $this->resetPage(); }

    private function publishedEvents(): Builder
    {
        $query = SchoolPost::query()
            ->where('type', 'event')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['school.district']);

        if (trim($this->search) !== '') {
            $term = '%'.mb_strtolower(trim($this->search)).'%';
            $query->where(function (Builder $search) use ($term) {
                $search->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(COALESCE(venue, '')) LIKE ?", [$term])
                    ->orWhereHas('school', fn (Builder $school) => $school->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        return $query;
    }

    public function render()
    {
        $base = $this->publishedEvents();
        $upcoming = (clone $base)->whereNotNull('starts_at')->where('starts_at', '>=', now())
            ->orderBy('starts_at')->paginate(9)->withQueryString();
        $past = (clone $base)->whereNotNull('starts_at')->where('starts_at', '<', now())
            ->orderByDesc('starts_at')->limit(4)->get();

        if ($this->period === 'past') {
            $upcoming = (clone $base)->whereNotNull('starts_at')->where('starts_at', '<', now())
                ->orderByDesc('starts_at')->paginate(9)->withQueryString();
        } elseif ($this->period === 'all') {
            $upcoming = (clone $base)->orderByRaw('CASE WHEN starts_at >= ? THEN 0 ELSE 1 END', [now()])
                ->orderBy('starts_at')->paginate(9)->withQueryString();
        }

        return view('livewire.opportunities.event-listing', [
            'events' => $upcoming,
            'pastEvents' => $past,
            'totalEvents' => (clone $base)->count(),
        ])->layout('layouts.app', [
            'title' => 'School events and open days in Uganda — FindURSCH',
            'description' => 'Find school open days, education fairs, conferences and community events across Uganda.',
        ]);
    }
}

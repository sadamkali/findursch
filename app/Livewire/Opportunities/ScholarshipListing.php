<?php

namespace App\Livewire\Opportunities;

use App\Models\SchoolPost;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ScholarshipListing extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $deadlineFilter = 'all';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedDeadlineFilter(): void { $this->resetPage(); }

    private function publishedScholarships(): Builder
    {
        $query = SchoolPost::query()
            ->where('type', 'bursary')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['school.district']);

        if (trim($this->search) !== '') {
            $term = '%'.mb_strtolower(trim($this->search)).'%';
            $query->where(function (Builder $search) use ($term) {
                $search->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(COALESCE(award, '')) LIKE ?", [$term])
                    ->orWhereHas('school', fn (Builder $school) => $school->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->deadlineFilter === 'open') {
            $query->where(fn (Builder $q) => $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString()));
        } elseif ($this->deadlineFilter === 'closed') {
            $query->whereDate('deadline', '<', now()->toDateString());
        }

        return $query;
    }

    public function render()
    {
        $query = $this->publishedScholarships();
        $openCount = (clone $query)->where(fn (Builder $q) => $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString()))->count();

        $scholarships = $query
            ->orderByRaw('CASE WHEN deadline IS NULL OR deadline >= ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderBy('deadline')
            ->paginate(12)
            ->withQueryString();

        return view('livewire.opportunities.scholarship-listing', compact('scholarships', 'openCount'))
            ->layout('layouts.app', [
                'title' => 'Scholarships and bursaries in Uganda — FindURSCH',
                'description' => 'Find school bursaries, student funding and scholarship opportunities published by verified school communities.',
            ]);
    }
}

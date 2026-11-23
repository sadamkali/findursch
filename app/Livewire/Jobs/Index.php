<?php

namespace App\Livewire\Jobs;

use App\Models\JobListing;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    private const CATEGORIES = [
        ['key' => 'teaching', 'label' => 'Teaching'],
        ['key' => 'administration', 'label' => 'Administration'],
        ['key' => 'higher', 'label' => 'Higher education'],
        ['key' => 'ngo', 'label' => 'NGO & education'],
        ['key' => 'support', 'label' => 'Support staff'],
        ['key' => 'other', 'label' => 'Other'],
    ];

    private const EMPLOYMENT_TYPES = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'temporary' => 'Temporary',
        'contract' => 'Contract',
        'internship' => 'Internship',
        'volunteer' => 'Volunteer',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $category = 'all';

    #[Url(as: 'loc', except: '')]
    public string $location = '';

    #[Url(as: 'type', except: '')]
    public string $employmentType = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedLocation(): void { $this->resetPage(); }
    public function updatedEmploymentType(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    public function setCategory(string $category): void
    {
        $allowed = array_column(self::CATEGORIES, 'key');
        $this->category = in_array($category, $allowed, true) ? $category : 'all';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'location', 'employmentType']);
        $this->category = 'all';
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    private function filteredJobs(): Builder
    {
        $query = JobListing::query()->publiclyVisible();

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }
        if ($this->location !== '') {
            $query->where('location', $this->location);
        }
        if ($this->employmentType !== '') {
            $query->where('employment_type', $this->employmentType);
        }

        $term = trim($this->search);
        if ($term !== '') {
            $pattern = '%'.mb_strtolower($term).'%';
            $query->where(function (Builder $search) use ($pattern) {
                $search->whereRaw('LOWER(title) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(employer) LIKE ?', [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(excerpt, '')) LIKE ?", [$pattern])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(location) LIKE ?', [$pattern]);
            });
        }

        return $query;
    }

    private function orderedJobs(Builder $query): Builder
    {
        if ($this->sort === 'closing') {
            return $query->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
                ->orderBy('deadline')
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at');
        }

        return $query->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $base = JobListing::query()->publiclyVisible();
        $filtered = $this->filteredJobs();
        $categoryCounts = (clone $base)->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category');
        $categories = collect(self::CATEGORIES)->map(fn (array $item) => array_merge($item, [
            'count' => (int) ($categoryCounts[$item['key']] ?? 0),
        ]));

        return view('livewire.jobs.index', [
            'jobs' => $this->orderedJobs($filtered)->paginate(12)->withQueryString(),
            'totalOpenJobs' => (clone $base)->count(),
            'filteredCount' => (clone $filtered)->count(),
            'categories' => $categories,
            'locations' => (clone $base)->select('location')->distinct()->orderBy('location')->pluck('location'),
            'employmentTypes' => self::EMPLOYMENT_TYPES,
            'closingSoon' => (clone $base)->whereNotNull('deadline')->orderBy('deadline')->limit(4)->get(),
        ])->layout('layouts.app', [
            'title' => 'Education jobs in Uganda — FindURSCH',
            'description' => 'Browse current teaching, school administration, higher education, NGO and education support vacancies across Uganda.',
        ]);
    }
}

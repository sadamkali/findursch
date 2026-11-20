<?php

namespace App\Livewire\Opportunities;

use App\Models\SchoolPost;
use App\Models\SchoolVideo;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ScholarshipListing extends Component
{
    use WithPagination;

    private const CATEGORIES = [
        ['key' => 'primary', 'label' => 'Primary', 'heading' => 'Primary school bursaries', 'terms' => ['primary', 'nursery']],
        ['key' => 'secondary', 'label' => 'Secondary', 'heading' => 'Secondary school bursaries', 'terms' => ['secondary', 'o-level', 'o level', 'a-level', 'a level']],
        ['key' => 'government', 'label' => 'Government', 'heading' => 'Government scholarships', 'terms' => ['government', 'state sponsorship']],
        ['key' => 'university', 'label' => 'University', 'heading' => 'University scholarships', 'terms' => ['university', 'undergraduate', 'postgraduate', 'tertiary', 'degree']],
        ['key' => 'international', 'label' => 'International', 'heading' => 'International & study-abroad awards', 'terms' => ['international', 'study abroad', 'overseas', 'abroad']],
        ['key' => 'foundations', 'label' => 'Foundations', 'heading' => 'NGO & foundation scholarships', 'terms' => ['foundation', 'ngo', 'non-governmental', 'charity']],
        ['key' => 'tvet', 'label' => 'TVET', 'heading' => 'TVET & vocational bursaries', 'terms' => ['tvet', 'vocational', 'technical training', 'skills training']],
        ['key' => 'other', 'label' => 'School bursaries', 'heading' => 'Other school bursaries', 'terms' => []],
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $category = 'all';

    #[Url(except: 'all')]
    public string $deadlineFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedDeadlineFilter(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $allowed = array_column(self::CATEGORIES, 'key');
        $allowed[] = 'all';
        $allowed[] = 'videos';

        $this->category = in_array($category, $allowed, true) ? $category : 'all';
        $this->resetPage();
    }

    private function basePublishedScholarships(): Builder
    {
        return SchoolPost::query()
            ->where('type', 'bursary')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible());
    }

    private function applyFilters(Builder $query): Builder
    {
        $term = trim($this->search);

        if ($term !== '') {
            $pattern = '%'.mb_strtolower($term).'%';
            $query->where(function (Builder $search) use ($pattern) {
                $search->whereRaw('LOWER(school_posts.title) LIKE ?', [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(school_posts.description, '')) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(school_posts.award, '')) LIKE ?", [$pattern])
                    ->orWhereHas('school', fn (Builder $school) => $school->whereRaw('LOWER(name) LIKE ?', [$pattern]));
            });
        }

        if ($this->deadlineFilter === 'open') {
            $query->where(fn (Builder $open) => $open
                ->whereNull('school_posts.deadline')
                ->orWhereDate('school_posts.deadline', '>=', now()->toDateString()));
        } elseif ($this->deadlineFilter === 'closed') {
            $query->whereDate('school_posts.deadline', '<', now()->toDateString());
        }

        return $query;
    }

    private function applyCategory(Builder $query, string $category): Builder
    {
        $definition = collect(self::CATEGORIES)->firstWhere('key', $category);

        if (! $definition) {
            return $query;
        }

        $expression = "LOWER(CONCAT_WS(' ', school_posts.title, school_posts.description, COALESCE(school_posts.award, '')))";

        if ($category === 'other') {
            foreach (self::CATEGORIES as $candidate) {
                foreach ($candidate['terms'] as $term) {
                    $query->whereRaw($expression.' NOT LIKE ?', ['%'.mb_strtolower($term).'%']);
                }
            }

            return $query;
        }

        return $query->where(function (Builder $match) use ($definition, $expression) {
            foreach ($definition['terms'] as $term) {
                $match->orWhereRaw($expression.' LIKE ?', ['%'.mb_strtolower($term).'%']);
            }
        });
    }

    private function orderScholarships(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN school_posts.deadline IS NULL OR school_posts.deadline >= ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderBy('school_posts.deadline')
            ->orderByDesc('school_posts.published_at');
    }

    private function publishedVideos(): Builder
    {
        $query = SchoolVideo::query()
            ->whereIn('status', ['approved', 'published'])
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible());

        if (trim($this->search) !== '') {
            $pattern = '%'.mb_strtolower(trim($this->search)).'%';
            $query->where(function (Builder $search) use ($pattern) {
                $search->whereRaw('LOWER(title) LIKE ?', [$pattern])
                    ->orWhereHas('school', fn (Builder $school) => $school->whereRaw('LOWER(name) LIKE ?', [$pattern]));
            });
        }

        return $query->with('school:id,name,slug')->latest();
    }

    public function render()
    {
        $openCount = (clone $this->basePublishedScholarships())
            ->where(fn (Builder $open) => $open
                ->whereNull('deadline')
                ->orWhereDate('deadline', '>=', now()->toDateString()))
            ->count();

        $filteredQuery = $this->applyFilters($this->basePublishedScholarships());
        $totalMatches = (clone $filteredQuery)->count();

        $groups = collect(self::CATEGORIES)->map(function (array $definition) {
            $query = $this->applyCategory(
                $this->applyFilters($this->basePublishedScholarships()),
                $definition['key']
            );

            $count = (clone $query)->count();
            $items = $this->orderScholarships(
                $query->with(['school.district', 'school.media'])
            )->limit(3)->get();

            return array_merge($definition, [
                'count' => $count,
                'items' => $items,
            ]);
        });

        $selectedCategory = collect(self::CATEGORIES)->firstWhere('key', $this->category);
        $selectedScholarships = null;

        if ($selectedCategory) {
            $query = $this->applyCategory(
                $this->applyFilters($this->basePublishedScholarships()),
                $this->category
            );

            $selectedScholarships = $this->orderScholarships(
                $query->with(['school.district', 'school.media'])
            )->paginate(12)->withQueryString();
        }

        $videos = $this->publishedVideos()->limit(3)->get();
        $videoCount = (clone $this->publishedVideos())->count();

        return view('livewire.opportunities.scholarship-listing', compact(
            'openCount',
            'groups',
            'selectedCategory',
            'selectedScholarships',
            'totalMatches',
            'videos',
            'videoCount',
        ))->layout('layouts.app', [
            'title' => 'Scholarships and bursaries in Uganda — FindURSCH',
            'description' => 'Explore published school bursaries and scholarship opportunities across Uganda. Check eligibility and deadlines directly with the publishing school.',
        ]);
    }
}

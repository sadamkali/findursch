<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\SchoolVideo;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NewsListing extends Component
{
    use WithPagination;

    #[Url]
    public ?int $category = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'latest')]
    public string $sort = 'latest';

    #[Url(except: 'news')]
    public string $view = 'news';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }
    public function updatedView(): void { $this->resetPage(); }

    private function publishedNewsQuery(): Builder
    {
        return Article::query()
            ->where('content_type', 'news')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->with(['category', 'author']);
    }

    private function newsCategories()
    {
        $publishedCategoryIds = (clone $this->publishedNewsQuery())
            ->select('category_id')
            ->whereNotNull('category_id')
            ->distinct();

        return ArticleCategory::query()
            ->where('is_active', true)
            ->whereIn('id', $publishedCategoryIds)
            ->orderBy('name')
            ->get();
    }

    private function applyFilters(Builder $query): Builder
    {
        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        $term = trim($this->search);
        if ($term !== '') {
            $pattern = '%'.mb_strtolower($term).'%';
            $query->where(function (Builder $search) use ($pattern) {
                $search->whereRaw('LOWER(title) LIKE ?', [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(excerpt, '')) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(body, '')) LIKE ?", [$pattern])
                    ->orWhereHas('category', fn (Builder $category) => $category->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                    ->orWhereHas('author', fn (Builder $author) => $author->whereRaw('LOWER(name) LIKE ?', [$pattern]));
            });
        }

        return $this->sort === 'popular'
            ? $query->orderByDesc('views_count')->orderByDesc('published_at')
            : $query->latest('published_at');
    }

    private function publishedVideos(): Builder
    {
        $query = SchoolVideo::query()
            ->whereIn('status', ['approved', 'published'])
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible())
            ->with('school');

        if (trim($this->search) !== '') {
            $pattern = '%'.mb_strtolower(trim($this->search)).'%';
            $query->where(function (Builder $search) use ($pattern) {
                $search->whereRaw('LOWER(title) LIKE ?', [$pattern])
                    ->orWhereHas('school', fn (Builder $school) => $school->whereRaw('LOWER(name) LIKE ?', [$pattern]));
            });
        }

        return $query->latest();
    }

    public function render()
    {
        $categories = $this->newsCategories();
        $base = $this->publishedNewsQuery();
        $filtered = $this->applyFilters(clone $base);

        $leadArticle = null;
        $secondaryArticle = null;
        $mostRead = collect();
        $categorySections = collect();
        $searchResults = null;

        if ($this->view !== 'videos') {
            $leadArticle = (clone $filtered)->reorder()->orderByDesc('is_featured')->latest('published_at')->first();

            $secondaryQuery = clone $filtered;
            if ($leadArticle) {
                $secondaryQuery->where('id', '!=', $leadArticle->id);
            }
            $secondaryArticle = $secondaryQuery->reorder()->orderByDesc('is_featured')->latest('published_at')->first();

            $mostRead = (clone $base)->reorder()
                ->orderByDesc('views_count')->latest('published_at')->limit(4)->get();

            if ($this->search === '' && ! $this->category) {
                foreach ($categories as $item) {
                    $articles = (clone $base)->where('category_id', $item->id)
                        ->latest('published_at')->limit(6)->get();

                    if ($articles->isNotEmpty()) {
                        $categorySections->push(['category' => $item, 'articles' => $articles]);
                    }
                }
            }

            if ($this->search !== '' || $this->category) {
                $searchResults = $this->applyFilters(clone $base)->paginate(12)->withQueryString();
            }
        }

        $videos = $this->publishedVideos()->limit(6)->get();
        $videoCount = (clone $this->publishedVideos())->count();
        $articleCount = (clone $base)->count();

        return view('livewire.articles.news-listing', [
            'categories' => $categories,
            'leadArticle' => $leadArticle,
            'secondaryArticle' => $secondaryArticle,
            'mostRead' => $mostRead,
            'categorySections' => $categorySections,
            'searchResults' => $searchResults,
            'selectedCategory' => $categories->firstWhere('id', $this->category),
            'videos' => $videos,
            'videoCount' => $videoCount,
            'articleCount' => $articleCount,
        ])->layout('layouts.app', [
            'title' => 'School news and updates in Uganda — FindURSCH',
            'description' => 'Education news, school announcements, policy updates and stories from school communities across Uganda.',
        ]);
    }
}

<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use App\Models\ArticleCategory;
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

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    private function newsCategories()
    {
        return ArticleCategory::query()
            ->where('is_active', true)
            ->whereIn('slug', ['school-news', 'education-policy'])
            ->orderBy('name')
            ->get();
    }

    private function publishedNewsQuery(): Builder
    {
        return Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn (Builder $query) => $query->whereIn('slug', ['school-news', 'education-policy']))
            ->with(['category', 'author']);
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
                    ->orWhereHas('category', fn (Builder $category) => $category->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                    ->orWhereHas('author', fn (Builder $author) => $author->whereRaw('LOWER(name) LIKE ?', [$pattern]));
            });
        }

        return $this->sort === 'popular'
            ? $query->orderByDesc('views_count')->orderByDesc('published_at')
            : $query->latest('published_at');
    }

    public function render()
    {
        $categories = $this->newsCategories();
        $base = $this->publishedNewsQuery();
        $filtered = $this->applyFilters(clone $base);

        $leadArticle = (clone $filtered)->reorder()->orderByDesc('is_featured')->latest('published_at')->first();
        $secondaryQuery = clone $filtered;
        if ($leadArticle) $secondaryQuery->where('id', '!=', $leadArticle->id);
        $secondaryArticle = $secondaryQuery->reorder()->orderByDesc('is_featured')->latest('published_at')->first();

        $mostRead = $this->applyFilters(clone $base)->reorder()
            ->orderByDesc('views_count')->latest('published_at')->limit(4)->get();

        $categorySections = collect();
        if ($this->search === '' && ! $this->category) {
            foreach ($categories as $item) {
                $articles = $this->applyFilters(clone $base)
                    ->where('category_id', $item->id)
                    ->limit(6)->get();

                if ($articles->isNotEmpty()) {
                    $categorySections->push(['category' => $item, 'articles' => $articles]);
                }
            }
        }

        $searchResults = null;
        if ($this->search !== '' || $this->category) {
            $searchResults = $this->applyFilters(clone $base)->paginate(12)->withQueryString();
        }

        return view('livewire.articles.article-listing', [
            'categories' => $categories,
            'leadArticle' => $leadArticle,
            'secondaryArticle' => $secondaryArticle,
            'mostRead' => $mostRead,
            'categorySections' => $categorySections,
            'searchResults' => $searchResults,
            'selectedCategory' => $categories->firstWhere('id', $this->category),
            'mode' => 'news',
        ])->layout('layouts.app', [
            'title' => 'School news and updates in Uganda — FindURSCH',
            'description' => 'Education news, school announcements and policy updates from schools and education communities across Uganda.',
        ]);
    }
}

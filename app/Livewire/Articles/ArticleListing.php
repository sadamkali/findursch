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
class ArticleListing extends Component
{
    use WithPagination;

    #[Url]
    public ?int $category = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'latest')]
    public string $sort = 'latest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    private function publishedArticlesQuery(): Builder
    {
        return Article::query()
            ->whereIn('content_type', ['blog', 'article'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['category', 'author']);
    }

    private function applyListingFilters(Builder $query): Builder
    {
        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        $term = trim($this->search);
        if ($term !== '') {
            $pattern = '%'.mb_strtolower($term).'%';

            $query->where(function (Builder $searchQuery) use ($pattern) {
                $searchQuery
                    ->whereRaw('LOWER(title) LIKE ?', [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(excerpt, '')) LIKE ?", [$pattern])
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                    ->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->whereRaw('LOWER(name) LIKE ?', [$pattern]));
            });
        }

        if ($this->sort === 'popular') {
            $query->orderByDesc('views_count')->orderByDesc('published_at');
        } else {
            $query->latest('published_at');
        }

        return $query;
    }

    public function render()
    {
        $publishedCategoryIds = (clone $this->publishedArticlesQuery())
            ->select('category_id')
            ->whereNotNull('category_id')
            ->distinct();

        $categories = ArticleCategory::query()
            ->where('is_active', true)
            ->whereIn('id', $publishedCategoryIds)
            ->orderBy('name')
            ->get();

        $filteredQuery = $this->applyListingFilters($this->publishedArticlesQuery());

        $leadArticle = (clone $filteredQuery)
            ->reorder()
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->first();

        $secondaryArticleQuery = clone $filteredQuery;
        if ($leadArticle) {
            $secondaryArticleQuery->where('id', '!=', $leadArticle->id);
        }

        $secondaryArticle = $secondaryArticleQuery
            ->reorder()
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->first();

        $mostRead = $this->applyListingFilters($this->publishedArticlesQuery())
            ->reorder()
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(4)
            ->get();

        $categorySections = collect();
        if ($this->search === '' && ! $this->category) {
            foreach ($categories as $item) {
                $articles = $this->applyListingFilters($this->publishedArticlesQuery())
                    ->where('category_id', $item->id)
                    ->limit(6)
                    ->get();

                if ($articles->isNotEmpty()) {
                    $categorySections->push([
                        'category' => $item,
                        'articles' => $articles,
                    ]);
                }
            }
        }

        $searchResults = null;
        if ($this->search !== '' || $this->category) {
            $searchResults = $this->applyListingFilters($this->publishedArticlesQuery())
                ->paginate(12)
                ->withQueryString();
        }

        return view('livewire.articles.article-listing', [
            'categories' => $categories,
            'leadArticle' => $leadArticle,
            'secondaryArticle' => $secondaryArticle,
            'mostRead' => $mostRead,
            'categorySections' => $categorySections,
            'searchResults' => $searchResults,
            'selectedCategory' => $categories->firstWhere('id', $this->category),
        ])->layout('layouts.app', [
            'title' => 'Blogs for parents, students and teachers — FindURSCH',
            'description' => 'Practical advice, inspiring stories and fresh ideas for parents, students and teachers across Uganda.',
        ]);
    }
}

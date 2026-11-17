<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use App\Models\ArticleCategory;
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

    public function render()
    {
        $query = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['category', 'author']);

        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        return view('livewire.articles.article-listing', [
            'articles' => $query->latest('published_at')->paginate(12),
            'categories' => ArticleCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

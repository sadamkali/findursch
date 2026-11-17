<?php

namespace App\Livewire\Articles;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ArticleListing extends Component
{
    use WithPagination;

    public $category = null;

    public function mount()
    {
        $this->category = request('category');
    }

    public function render()
    {
        $query = \App\Models\Article::where('status', 'published')
            ->where('published_at', '<=', now())
            ->with('category', 'author');

        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        $articles = $query->latest('published_at')->paginate(12);
        $categories = \App\Models\ArticleCategory::where('is_active', true)->get();

        return view('livewire.articles.article-listing', compact('articles', 'categories'));
    }
}
<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ArticleShow extends Component
{
    public $article;

    public function mount(Article $article)
    {
        $this->article = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereKey($article->id)
            ->firstOrFail();

        $this->article->load('category', 'author');
        $this->article->increment('views_count');
    }

    public function render()
    {
        $related = Article::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('category_id', $this->article->category_id)
            ->where('id', '!=', $this->article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('livewire.articles.article-show', compact('related'));
    }
}
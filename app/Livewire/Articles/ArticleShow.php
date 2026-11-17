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
        $this->article = $article;
        $this->article->load('category', 'author');
        $this->article->increment('views_count');
    }

    public function render()
    {
        $related = \App\Models\Article::where('status', 'published')
            ->where('category_id', $this->article->category_id)
            ->where('id', '!=', $this->article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('livewire.articles.article-show', compact('related'));
    }
}
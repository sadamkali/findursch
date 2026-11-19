<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use App\Models\School;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ArticleShow extends Component
{
    public Article $article;

    public function mount(Article $article): void
    {
        $this->article = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereKey($article->id)
            ->firstOrFail();

        $this->article->load(['category', 'author']);
        $this->article->increment('views_count');
    }

    public function render()
    {
        $published = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $related = (clone $published)
            ->where('category_id', $this->article->category_id)
            ->where('id', '!=', $this->article->id)
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $mostRead = (clone $published)
            ->where('category_id', $this->article->category_id)
            ->with('category')
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $spotlightSchool = School::query()
            ->publiclyVisible()
            ->with('district')
            ->featured()
            ->first();

        return view('livewire.articles.article-show', compact('related', 'mostRead', 'spotlightSchool'))
            ->layout('layouts.app', [
                'title' => $this->article->meta_title ?: $this->article->title.' — FindURSCH',
                'description' => $this->article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($this->article->excerpt ?: $this->article->body), 155),
            ]);
    }
}

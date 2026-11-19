<?php

namespace App\Livewire\Home;

use App\Models\Article;
use App\Models\District;
use App\Models\Review;
use App\Models\School;
use App\Models\SchoolPost;
use App\Models\SchoolType;
use App\Models\SchoolVideo;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class HomePage extends Component
{
    public function render()
    {
        $publicSchools = fn ($query) => $query->publiclyVisible();

        $schoolTypes = SchoolType::query()
            ->where('is_active', true)
            ->withCount(['schools as public_schools_count' => $publicSchools])
            ->orderBy('order')
            ->get();

        $featuredSchools = School::query()
            ->publiclyVisible()
            ->with(['district', 'type', 'curricula', 'media'])
            ->orderByDesc('is_featured')
            ->latest('created_at')
            ->limit(3)
            ->get();

        $spotlightSchool = School::query()
            ->publiclyVisible()
            ->with(['district', 'type', 'curricula', 'media'])
            ->whereNotIn('id', $featuredSchools->pluck('id'))
            ->orderByDesc('is_featured')
            ->latest('created_at')
            ->first();

        if (! $spotlightSchool) {
            $spotlightSchool = $featuredSchools->first();
        }

        $districts = District::query()
            ->with('region')
            ->withCount(['schools as public_listing_count' => $publicSchools])
            ->orderByDesc('public_listing_count')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $searchDistricts = District::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $publishedArticles = Article::query()
            ->published()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $publishedNews = (clone $publishedArticles)
            ->whereHas('category', fn ($query) => $query->whereIn('slug', ['school-news', 'education-policy']))
            ->with(['category', 'author']);

        $leadArticle = (clone $publishedNews)
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->first();

        $newsArticlesQuery = (clone $publishedNews)->latest('published_at');

        if ($leadArticle) {
            $newsArticlesQuery->where('id', '!=', $leadArticle->id);
        }

        $newsArticles = $newsArticlesQuery->limit(3)->get();

        $upcomingEvents = SchoolPost::query()
            ->where('type', 'event')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->whereHas('school', fn ($query) => $query->publiclyVisible())
            ->with('school')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        $featuredVideos = SchoolVideo::query()
            ->whereIn('status', ['approved', 'published'])
            ->whereHas('school', fn ($query) => $query->publiclyVisible())
            ->with('school:id,name,slug')
            ->latest()
            ->limit(2)
            ->get();

        $featuredBursaries = SchoolPost::query()
            ->where('type', 'bursary')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString()))
            ->whereHas('school', fn ($query) => $query->publiclyVisible())
            ->with('school:id,name,slug,district_id')
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $latestSchoolUpdates = SchoolPost::query()
            ->where('type', 'news')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('school', fn ($query) => $query->publiclyVisible())
            ->with('school:id,name,slug')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $featuredReviews = Review::query()
            ->where('status', 'approved')
            ->whereNotNull('body')
            ->where('body', '!=', '')
            ->whereHas('school', fn ($query) => $query->publiclyVisible())
            ->with(['school:id,name,slug', 'user:id,name'])
            ->latest()
            ->limit(2)
            ->get();

        $insightArticlesQuery = (clone $publishedArticles)
            ->with(['category', 'author'])
            ->latest('published_at');

        $excludedArticleIds = collect([$leadArticle?->id])
            ->merge($newsArticles->pluck('id'))
            ->filter()
            ->values()
            ->all();

        if ($excludedArticleIds !== []) {
            $insightArticlesQuery->whereNotIn('id', $excludedArticleIds);
        }

        $insightArticles = $insightArticlesQuery->limit(4)->get();

        $stats = [
            'schools' => School::query()->publiclyVisible()->count(),
            'districts' => District::query()->count(),
            'parents' => User::query()->where('user_type', 'parent')->count(),
            'reviews' => Review::query()->where('status', 'approved')->count(),
        ];

        return view('livewire.home.home-page', compact(
            'schoolTypes',
            'featuredSchools',
            'spotlightSchool',
            'districts',
            'searchDistricts',
            'leadArticle',
            'newsArticles',
            'upcomingEvents',
            'featuredVideos',
            'featuredBursaries',
            'latestSchoolUpdates',
            'featuredReviews',
            'insightArticles',
            'stats',
        ));
    }
}

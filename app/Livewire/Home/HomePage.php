<?php

namespace App\Livewire\Home;

use App\Models\Article;
use App\Models\District;
use App\Models\Review;
use App\Models\School;
use App\Models\SchoolPost;
use App\Models\SchoolAdvertisement;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolType;
use App\Models\SchoolVideo;
use App\Models\Setting;
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
            ->limit(9)
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

        $homepageSettings = Setting::query()
            ->whereIn('key', [
                'homepage_hero_image_url', 'homepage_slides',
                'homepage_register_tab_enabled', 'homepage_search_enabled',
                'homepage_featured_schools_enabled', 'homepage_sidebar_enabled',
                'homepage_categories_enabled', 'homepage_upcoming_events_enabled',
                'homepage_news_enabled', 'homepage_videos_enabled',
                'homepage_scholarships_enabled', 'homepage_reviews_enabled',
                'homepage_insights_enabled', 'homepage_banner_ads_enabled',
                'homepage_cta_enabled', 'primary_color', 'accent_color',
                'font_display', 'font_body',
            ])
            ->pluck('value', 'key');

        $heroImageUrl = (string) ($homepageSettings['homepage_hero_image_url'] ?? '');
        $storedSlides = json_decode((string) ($homepageSettings['homepage_slides'] ?? '[]'), true);
        $homepageSlides = collect(is_array($storedSlides) ? $storedSlides : [])
            ->filter(fn ($slide) => is_array($slide) && ($slide['enabled'] ?? true) && filled($slide['image_url'] ?? null))
            ->map(fn ($slide) => [
                'image_url' => (string) ($slide['image_url'] ?? ''),
                'title' => (string) ($slide['title'] ?? 'Find the right school for your child'),
                'description' => (string) ($slide['description'] ?? ''),
                'button_label' => (string) ($slide['button_label'] ?? ''),
                'button_url' => (string) ($slide['button_url'] ?? ''),
            ])
            ->values()
            ->all();

        if ($homepageSlides === []) {
            $homepageSlides = [[
                'image_url' => $heroImageUrl,
                'title' => 'Find the right school for your child',
                'description' => 'Browse nursery, primary, secondary, vocational and university listings across Uganda, and compare them side by side.',
                'button_label' => '',
                'button_url' => '',
            ]];
        }

        $displayKeys = [
            'register_tab' => 'homepage_register_tab_enabled',
            'search' => 'homepage_search_enabled',
            'featured_schools' => 'homepage_featured_schools_enabled',
            'sidebar' => 'homepage_sidebar_enabled',
            'categories' => 'homepage_categories_enabled',
            'upcoming_events' => 'homepage_upcoming_events_enabled',
            'news' => 'homepage_news_enabled',
            'videos' => 'homepage_videos_enabled',
            'scholarships' => 'homepage_scholarships_enabled',
            'reviews' => 'homepage_reviews_enabled',
            'insights' => 'homepage_insights_enabled',
            'banner_ads' => 'homepage_banner_ads_enabled',
            'cta' => 'homepage_cta_enabled',
        ];
        $homepageDisplaySettings = collect($displayKeys)->mapWithKeys(
            fn ($key, $name) => [$name => filter_var($homepageSettings[$key] ?? true, FILTER_VALIDATE_BOOLEAN)]
        )->all();

        $validColor = fn ($value, $fallback) => is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
        $homepageTheme = [
            'primary_color' => $validColor($homepageSettings['primary_color'] ?? null, '#0c5a49'),
            'accent_color' => $validColor($homepageSettings['accent_color'] ?? null, '#f0b030'),
            'font_display' => in_array($homepageSettings['font_display'] ?? '', ['Playfair Display', 'DM Serif Display'], true) ? $homepageSettings['font_display'] : 'Playfair Display',
            'font_body' => in_array($homepageSettings['font_body'] ?? '', ['Inter', 'system-ui'], true) ? $homepageSettings['font_body'] : 'Inter',
        ];

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
            'heroImageUrl',
            'homepageSlides',
            'homepageDisplaySettings',
            'homepageTheme',
            'stats',
        ));
    }
}

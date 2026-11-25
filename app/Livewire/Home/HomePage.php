<?php

namespace App\Livewire\Home;

use App\Models\Article;
use App\Models\JobListing;
use App\Models\Review;
use App\Models\School;
use App\Models\SchoolPost;
use App\Models\SchoolType;
use App\Models\SchoolVideo;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class HomePage extends Component
{
    public function render()
    {
        $publicSchools = School::query()->publiclyVisible();

        $featuredSchools = (clone $publicSchools)
            ->with(['district', 'type', 'curricula', 'media'])
            ->orderByDesc('is_featured')
            ->orderByRaw('CASE WHEN featured_until IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('featured_until')
            ->latest('created_at')
            ->limit(9)
            ->get();

        $spotlightQuery = (clone $publicSchools)->with(['district', 'type', 'curricula', 'media']);
        if ($featuredSchools->isNotEmpty()) {
            $spotlightQuery->whereNotIn('id', $featuredSchools->take(3)->pluck('id'));
        }
        $spotlightSchool = $spotlightQuery->orderByDesc('is_featured')->latest('created_at')->first();

        $schoolTypes = SchoolType::query()
            ->where('is_active', true)
            ->whereHas('schools', fn (Builder $schools) => $schools->publiclyVisible())
            ->withCount(['schools as public_listing_count' => fn (Builder $schools) => $schools->publiclyVisible()])
            ->orderBy('order')
            ->limit(6)
            ->get();

        $publishedArticles = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true))
            ->with(['category', 'author']);

        $newsArticles = (clone $publishedArticles)
            ->orderByDesc('is_featured')->latest('published_at')->limit(6)->get();

        $insightArticles = (clone $publishedArticles)
            ->whereNotIn('id', $newsArticles->take(3)->pluck('id'))
            ->orderByDesc('is_featured')->latest('published_at')->limit(4)->get();

        $partnerSchoolNews = SchoolPost::query()
            ->where('type', 'news')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible())
            ->with(['school.district', 'school.media'])
            ->latest('published_at')->first();

        $upcomingEvents = SchoolPost::query()
            ->where('type', 'event')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible())
            ->with('school.district')
            ->orderBy('starts_at')->limit(3)->get();

        $scholarshipCount = SchoolPost::query()
            ->where('type', 'bursary')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $open) => $open->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible())
            ->count();

        $latestJobs = JobListing::query()->publiclyVisible()
            ->orderByDesc('is_featured')->latest('published_at')->limit(3)->get();

        $videos = SchoolVideo::query()
            ->whereIn('status', ['approved', 'published'])
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible())
            ->with('school')->latest()->limit(4)->get();

        $reviews = Review::query()
            ->where('status', 'approved')
            ->whereHas('school', fn (Builder $school) => $school->publiclyVisible())
            ->with(['school', 'user'])->latest()->limit(3)->get();

        $homepageCarouselSettings = Setting::query()
            ->whereIn('key', ['homepage_slides', 'homepage_hero_image_url'])
            ->pluck('value', 'key');

        $storedHomepageSlides = json_decode((string) ($homepageCarouselSettings['homepage_slides'] ?? '[]'), true);
        $storedHomepageSlides = is_array($storedHomepageSlides) ? $storedHomepageSlides : [];

        $homepageSlides = collect($storedHomepageSlides)
            ->filter(fn ($slide) => is_array($slide) && filter_var($slide['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN))
            ->map(function (array $slide): array {
                $imageUrl = trim((string) ($slide['image_url'] ?? ''));
                $imageScheme = $imageUrl !== '' ? strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)) : '';
                if (! filter_var($imageUrl, FILTER_VALIDATE_URL) || ! in_array($imageScheme, ['https', 'http'], true)) {
                    $imageUrl = '';
                }

                $buttonUrl = trim((string) ($slide['button_url'] ?? ''));
                $isInternalPath = str_starts_with($buttonUrl, '/') && ! str_starts_with($buttonUrl, '//');
                $buttonScheme = $buttonUrl !== '' ? strtolower((string) parse_url($buttonUrl, PHP_URL_SCHEME)) : '';
                $isSecureExternalUrl = filter_var($buttonUrl, FILTER_VALIDATE_URL) && $buttonScheme === 'https';
                if (! $isInternalPath && ! $isSecureExternalUrl) {
                    $buttonUrl = '';
                }

                return [
                    'image_url' => $imageUrl,
                    'title' => trim((string) ($slide['title'] ?? '')) ?: 'Find the right school for your child',
                    'description' => trim((string) ($slide['description'] ?? '')),
                    'button_label' => trim((string) ($slide['button_label'] ?? '')),
                    'button_url' => $buttonUrl,
                ];
            })
            ->take(8)
            ->values()
            ->all();

        if ($homepageSlides === []) {
            $legacyHeroImage = trim((string) ($homepageCarouselSettings['homepage_hero_image_url'] ?? ''));
            $legacyScheme = $legacyHeroImage !== '' ? strtolower((string) parse_url($legacyHeroImage, PHP_URL_SCHEME)) : '';
            if (! filter_var($legacyHeroImage, FILTER_VALIDATE_URL) || ! in_array($legacyScheme, ['https', 'http'], true)) {
                $legacyHeroImage = '';
            }

            $homepageSlides = [[
                'image_url' => $legacyHeroImage,
                'title' => 'Find the right school for your child',
                'description' => 'Browse nursery, primary, secondary, vocational and university listings across Uganda, and compare them side by side.',
                'button_label' => '',
                'button_url' => '',
            ]];
        }

        $firstHeroSlide = $homepageSlides[0];

        $savedSchoolIds = [];
        $homepageSchoolIds = $featuredSchools->pluck('id')
            ->concat(collect([$spotlightSchool?->id]))
            ->filter()
            ->unique()
            ->values();

        if (auth()->check() && $homepageSchoolIds->isNotEmpty()) {
            $savedSchoolIds = auth()->user()->savedSchools()
                ->whereIn('schools.id', $homepageSchoolIds)
                ->pluck('schools.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('livewire.home.home-page', compact(
            'featuredSchools', 'spotlightSchool', 'schoolTypes', 'newsArticles',
            'insightArticles', 'partnerSchoolNews', 'upcomingEvents', 'scholarshipCount',
            'latestJobs', 'videos', 'reviews', 'savedSchoolIds',
            'homepageSlides', 'firstHeroSlide'
        ))->layout('layouts.app', [
            'title' => 'FindURSCH — Find the right school in Uganda',
            'description' => 'Find the right school for your child. Explore verified school profiles, compare options, read education news, find events, watch school videos and discover scholarships and jobs across Uganda.',
        ]);
    }
}

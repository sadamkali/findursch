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

        $footerSettings = Setting::query()
            ->whereIn('key', ['site_name', 'site_description', 'contact_address', 'support_phone', 'support_email', 'site_footer_copyright_text'])
            ->pluck('value', 'key');

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
            'latestJobs', 'videos', 'reviews', 'footerSettings', 'savedSchoolIds'
        ))->layout('layouts.app', [
            'title' => 'FindURSCH — Find the right school in Uganda',
            'description' => 'Find the right school for your child. Explore verified school profiles, compare options, read education news, find events, watch school videos and discover scholarships and jobs across Uganda.',
        ]);
    }
}

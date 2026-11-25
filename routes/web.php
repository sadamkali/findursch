<?php

use App\Livewire\Articles\ArticleListing;
use App\Livewire\Articles\NewsListing;
use App\Livewire\Opportunities\EventListing;
use App\Livewire\Opportunities\EventShow;
use App\Livewire\Opportunities\ScholarshipListing;
use App\Livewire\Opportunities\ScholarshipShow;
use App\Livewire\Opportunities\SchoolPostShow;
use App\Livewire\Jobs\Index as JobIndex;
use App\Livewire\Jobs\Show as JobShow;
use App\Livewire\Articles\ArticleShow;
use App\Livewire\Districts\DistrictSchools;
use App\Livewire\Home\HomePage;
use App\Livewire\Schools\SchoolDetail;
use App\Livewire\Search\SearchResults;
use App\Livewire\User\AccountHome;
use App\Livewire\User\MyInquiries;
use App\Livewire\User\MyReviews;
use App\Livewire\User\ProfileEdit;
use App\Livewire\User\SavedSchools;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SchoolDirectoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SchoolRequirementDownloadController;
use App\Http\Controllers\Api\AdManagerController;
use App\Models\School;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

$dashboardUrl = static fn (): string => rtrim((string) config('services.dashboard.url', 'https://admin.riotinvestments.space'), '/');
$accountDashboardUrl = static fn (): string => rtrim((string) config('services.dashboard.account_url', 'https://account.riotinvestments.space'), '/');

// =============== PUBLIC WEBSITE ===============
Route::get('/', HomePage::class)->name('home');
Route::get('/schools', SchoolDirectoryController::class)->name('schools.index');
Route::get('/schools/{school:slug}', SchoolDetail::class)->name('schools.show');
Route::get('/schools/{school:slug}/requirements/{document}/download', SchoolRequirementDownloadController::class)
    ->whereNumber('document')->middleware('throttle:20,1')->name('schools.requirements.download');
Route::get('/district/{district:slug}', DistrictSchools::class)->name('district.show');
Route::get('/search', SearchResults::class)->name('search');
Route::get('/api/search/suggest', function (Request $request) {
    $term = trim((string) $request->query('q', ''));

    if (mb_strlen($term) < 2 || mb_strlen($term) > 100) {
        return response()->json(['data' => []]);
    }

    $pattern = '%'.mb_strtolower($term).'%';

    $schools = School::query()
        ->publiclyVisible()
        ->whereRaw('LOWER(name) LIKE ?', [$pattern])
        ->with('district')
        ->limit(5)
        ->get(['id', 'name', 'slug', 'district_id'])
        ->map(fn (School $school) => [
            'type' => 'school',
            'label' => $school->name,
            'detail' => $school->district?->name,
            'url' => route('schools.show', $school),
        ]);

    $districts = District::query()
        ->whereRaw('LOWER(name) LIKE ?', [$pattern])
        ->limit(4)
        ->get(['id', 'name', 'slug'])
        ->map(fn (District $district) => [
            'type' => 'district',
            'label' => $district->name,
            'detail' => 'District',
            'url' => route('district.show', $district),
        ]);

    return response()->json(['data' => $schools->concat($districts)->values()]);
})->middleware('throttle:30,1')->name('search.suggest');

Route::redirect('/compare', '/schools', 302)->name('compare');
Route::get('/articles', ArticleListing::class)->name('articles.index');
Route::get('/articles/{article:slug}', ArticleShow::class)->name('articles.show');
Route::get('/news', NewsListing::class)->name('news.index');
Route::get('/news/{article:slug}', ArticleShow::class)->name('news.show');
Route::get('/school-news/{post}', SchoolPostShow::class)->name('school-news.show');
Route::get('/events', EventListing::class)->name('events.index');
Route::get('/events/{post}', EventShow::class)->name('events.show');
Route::get('/jobs', JobIndex::class)->name('jobs.index');
Route::get('/jobs/{job:slug}', JobShow::class)->name('jobs.show');
Route::get('/scholarships', ScholarshipListing::class)->name('scholarships.index');
Route::get('/scholarships/{post}', ScholarshipShow::class)->name('scholarships.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('legal.terms');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/ad-banner/{banner}/click', [AdManagerController::class, 'click'])->whereNumber('banner')->name('site-ad-banner.click');

// =============== CENTRALIZED AUTHENTICATION ===============
Route::get('/login', function (Request $request) use ($accountDashboardUrl) {
    $intended = (string) $request->session()->pull('url.intended', '/account');
    $path = parse_url($intended, PHP_URL_PATH) ?: '/account';

    if (! str_starts_with($path, '/') || str_starts_with($path, '//') ||
        preg_match('#^/(login|register|auth/dashboard-callback|auth/dashboard-login)(/|$)#', $path)) {
        $path = '/account';
    }

    $dashboardIdentity = $request->session()->get('dashboard_identity');
    if (is_array($dashboardIdentity) && in_array($dashboardIdentity['role'] ?? null, ['admin', 'school_owner'], true)) {
        return redirect()->route('auth.dashboard-login', ['returnTo' => $path]);
    }

    return redirect()->away($accountDashboardUrl().'/login?returnTo='.rawurlencode($path));
})->name('login');

Route::get('/register', function (Request $request) use ($accountDashboardUrl) {
    $dashboardIdentity = $request->session()->get('dashboard_identity');
    if (is_array($dashboardIdentity) && in_array($dashboardIdentity['role'] ?? null, ['admin', 'school_owner'], true)) {
        return redirect()->route('auth.dashboard-login');
    }

    return redirect()->away($accountDashboardUrl().'/register?type=school_owner&returnTo=%2Faccount');
})->name('register');

Route::get('/forgot-password', fn () => redirect()->away($accountDashboardUrl().'/login'))->name('password.request');
Route::get('/reset-password/{token}', function (string $token, Request $request) use ($accountDashboardUrl) {
    $email = rawurlencode((string) $request->query('email', ''));
    return redirect()->away($accountDashboardUrl().'/reset-password/'.$token.'?email='.$email);
})->name('password.reset');

// A short-lived, single-use Sanctum token transfers the parent session back to this origin.
Route::get('/auth/dashboard-callback', function (Request $request) {
    $plainTextToken = (string) $request->query('code', '');
    abort_if ($plainTextToken === '', 403);

    $accessToken = PersonalAccessToken::findToken($plainTextToken);
    abort_unless(
        $accessToken &&
        $accessToken->can('public-session-handoff') &&
        $accessToken->expires_at &&
        $accessToken->expires_at->isFuture(),
        403
    );

    $user = $accessToken->tokenable;
    abort_unless($user instanceof User && $user->status === 'active', 403);

    $isParent = $user->hasRole('parent');
    $isSchoolOwner = $user->hasRole('school_owner');
    $isAdmin = $user->hasRole('admin') || $user->getAllPermissions()->contains(
        fn ($permission) => str_starts_with($permission->name, 'admin.')
    );
    abort_unless($isParent || $isSchoolOwner || $isAdmin, 403);

    $accessToken->delete();

    if ($isParent) {
        Auth::login($user);
        $request->session()->forget('dashboard_identity');
    } else {
        // Owners and administrators need a public-site navigation identity,
        // not a parent login or access to parent-only account pages.
        Auth::logout();
        $request->session()->put('dashboard_identity', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $isAdmin ? 'admin' : 'school_owner',
        ]);
    }

    $request->session()->regenerate();

    $redirect = (string) $request->query('redirect', '/account');
    if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//') ||
        str_contains($redirect, "\r") || str_contains($redirect, "\n")) {
        $redirect = '/account';
    }

    return redirect()->to($redirect)
        ->header('Referrer-Policy', 'no-referrer')
        ->header('Cache-Control', 'no-store, private');
})->name('auth.dashboard-callback');

// Re-establish a host-only dashboard session from the public site's verified
// server-side login identity. The exchanged code is single-use and short-lived.
Route::get('/auth/dashboard-login', function (Request $request) use ($dashboardUrl, $accountDashboardUrl) {
    $identity = $request->session()->get('dashboard_identity');
    abort_unless(is_array($identity) && in_array($identity['role'] ?? null, ['admin', 'school_owner'], true), 401);

    $user = User::query()->find($identity['id'] ?? null);
    abort_unless($user instanceof User && $user->status === 'active', 401);

    $isAdmin = $user->hasRole('admin') || $user->getAllPermissions()->contains(
        fn ($permission) => str_starts_with($permission->name, 'admin.')
    );
    $isSchoolOwner = $user->hasRole('school_owner');
    abort_unless($isAdmin || $isSchoolOwner, 403);

    $returnTo = (string) $request->query('returnTo', $isAdmin ? '/admin' : '/owner');
    if (! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//') ||
        str_contains($returnTo, "\r") || str_contains($returnTo, "\n")) {
        $returnTo = $isAdmin ? '/admin' : '/owner';
    }

    $code = $user->createToken(
        'findursch-dashboard-session-handoff',
        ['dashboard-session-handoff'],
        now()->addMinutes(2)
    )->plainTextToken;

    $workspaceUrl = $isAdmin ? $dashboardUrl() : $accountDashboardUrl();
    $callback = $workspaceUrl.'/auth/public-callback?code='.rawurlencode($code).'&redirect='.rawurlencode($returnTo);

    return redirect()->away($callback)
        ->header('Referrer-Policy', 'no-referrer')
        ->header('Cache-Control', 'no-store, private');
})->name('auth.dashboard-login');

// Dashboard logout also clears the public-site navigation identity so the
// next visit cannot silently create another workspace session.
Route::get('/auth/dashboard-logout', function (Request $request) use ($dashboardUrl, $accountDashboardUrl) {
    $request->session()->forget('dashboard_identity');
    $workspace = $request->query('workspace') === 'admin' ? 'admin' : 'owner';
    $returnTo = $workspace === 'admin'
        ? $dashboardUrl().'/admin/login?notice=signed-out'
        : $accountDashboardUrl().'/login?notice=signed-out';

    return redirect()->away($returnTo)
        ->header('Cache-Control', 'no-store, private');
})->name('auth.dashboard-logout');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout')->middleware('auth');

// =============== PARENT ACCOUNT FEATURES ===============
Route::middleware(['auth'])->prefix('account')->name('user.')->group(function () {
    Route::get('/', AccountHome::class)->name('home');
    Route::get('/saved-schools', SavedSchools::class)->name('saved-schools');
    Route::get('/inquiries', MyInquiries::class)->name('inquiries');
    Route::get('/reviews', MyReviews::class)->name('reviews');
    Route::get('/profile', ProfileEdit::class)->name('profile');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/schools/{school}/inquire', [\App\Http\Controllers\InquiryController::class, 'store'])->name('inquiry.store');
    Route::post('/schools/{school}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('review.store');
    Route::post('/schools/{school}/save', [\App\Http\Controllers\SavedSchoolController::class, 'toggle'])->name('school.save.toggle');
});

// Legacy dashboard URLs are permanent entry-point redirects to the dedicated Next.js app.
Route::get('/admin/{path?}', function (?string $path = null) use ($dashboardUrl) {
    $target = '/admin'.($path ? '/'.ltrim($path, '/') : '');
    return redirect()->away($dashboardUrl().$target);
})->where('path', '.*');

Route::get('/owner/{path?}', function (?string $path = null) use ($accountDashboardUrl) {
    return redirect()->away($accountDashboardUrl().'/owner');
})->where('path', '.*');

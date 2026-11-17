<?php

use App\Livewire\Articles\ArticleListing;
use App\Livewire\Articles\ArticleShow;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Compare\CompareSchools;
use App\Livewire\Districts\DistrictSchools;
use App\Livewire\Home\HomePage;
use App\Livewire\Schools\SchoolDetail;
use App\Livewire\Schools\SchoolListing;
use App\Livewire\Search\SearchResults;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\MyInquiries;
use App\Livewire\User\MyReviews;
use App\Livewire\User\ProfileEdit;
use App\Livewire\User\SavedSchools;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureSchoolOwner;
use App\Livewire\Owner\Dashboard as OwnerDashboard;
use App\Livewire\Owner\SchoolEditor;
use App\Livewire\Owner\SchoolDetails;
use App\Livewire\Owner\Inquiries as OwnerInquiries;
use App\Livewire\Owner\Reviews as OwnerReviews;
use App\Models\School;
use App\Models\District;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =============== PUBLIC ROUTES ===============
Route::get('/', HomePage::class)->name('home');
Route::get('/schools', SchoolListing::class)->name('schools.index');
Route::get('/schools/{school:slug}', SchoolDetail::class)->name('schools.show');
Route::get('/district/{district:slug}', DistrictSchools::class)->name('district.show');
Route::get('/search', SearchResults::class)->name('search');
Route::get('/api/search/suggest', function (Request $request) {
    $term = trim((string) $request->query('q', ''));

    if (mb_strlen($term) < 2 || mb_strlen($term) > 100) {
        return response()->json(['data' => []]);
    }

    $pattern = '%'.mb_strtolower($term).'%';

    $schools = School::query()
        ->active()
        ->verified()
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

    return response()->json([
        'data' => $schools->concat($districts)->values(),
    ]);
})->middleware('throttle:30,1')->name('search.suggest');

Route::get('/compare', CompareSchools::class)->name('compare');
Route::get('/articles', ArticleListing::class)->name('articles.index');
Route::get('/articles/{article:slug}', ArticleShow::class)->name('articles.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('legal.terms');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// =============== GUEST AUTHENTICATION ===============
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// =============== EMAIL VERIFICATION ===============
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('livewire.auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        $destination = $request->user()->hasRole('school_owner')
            ? 'owner.dashboard'
            : 'user.dashboard';

        return redirect()->route($destination)
            ->with('status', 'Your email address has been verified.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'Your email address is already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A fresh verification link has been sent to your email address.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout')->middleware('auth');

// =============== VERIFIED USER ACCOUNT ===============
Route::middleware(['auth', 'verified'])->prefix('account')->name('user.')->group(function () {
    Route::get('/', UserDashboard::class)->name('dashboard');
    Route::get('/saved-schools', SavedSchools::class)->name('saved-schools');
    Route::get('/inquiries', MyInquiries::class)->name('inquiries');
    Route::get('/reviews', MyReviews::class)->name('reviews');
    Route::get('/profile', ProfileEdit::class)->name('profile');
});

// =============== VERIFIED USER ACTIONS ===============
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/schools/{school}/inquire', [\App\Http\Controllers\InquiryController::class, 'store'])->name('inquiry.store');
    Route::post('/schools/{school}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('review.store');
    Route::post('/schools/{school}/save', [\App\Http\Controllers\SavedSchoolController::class, 'toggle'])->name('school.save.toggle');
});

// =============== SCHOOL OWNER AREA ===============
Route::middleware(['auth', 'verified', EnsureSchoolOwner::class])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('/', OwnerDashboard::class)->name('dashboard');
        Route::get('/schools/create', SchoolEditor::class)->name('schools.create');
        Route::get('/schools/{school}/edit', SchoolEditor::class)->name('schools.edit');
        Route::get('/schools/{school}/details', SchoolDetails::class)->name('schools.details');
        Route::get('/inquiries', OwnerInquiries::class)->name('inquiries');
        Route::get('/reviews', OwnerReviews::class)->name('reviews');
    });

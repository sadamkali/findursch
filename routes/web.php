<?php

use App\Livewire\Home\HomePage;
use App\Livewire\Schools\SchoolListing;
use App\Livewire\Schools\SchoolDetail;
use App\Livewire\Districts\DistrictSchools;
use App\Livewire\Search\SearchResults;
use App\Livewire\Compare\CompareSchools;
use App\Livewire\Articles\ArticleListing;
use App\Livewire\Articles\ArticleShow;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\SavedSchools;
use App\Livewire\User\MyInquiries;
use App\Livewire\User\MyReviews;
use App\Livewire\User\ProfileEdit;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// =============== PUBLIC ROUTES ===============
Route::get('/', HomePage::class)->name('home');
Route::get('/schools', SchoolListing::class)->name('schools.index');
Route::get('/schools/{school:slug}', SchoolDetail::class)->name('schools.show');
Route::get('/district/{district:slug}', DistrictSchools::class)->name('district.show');
Route::get('/search', SearchResults::class)->name('search');
Route::get('/compare', CompareSchools::class)->name('compare');
Route::get('/articles', ArticleListing::class)->name('articles.index');
Route::get('/articles/{article:slug}', ArticleShow::class)->name('articles.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('legal.terms');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// =============== AUTH ===============
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', [PageController::class, 'forgotPassword'])->name('password.request');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout')->middleware('auth');

// =============== USER (auth) ===============
Route::middleware(['auth'])->prefix('account')->name('user.')->group(function () {
    Route::get('/', UserDashboard::class)->name('dashboard');
    Route::get('/saved-schools', SavedSchools::class)->name('saved-schools');
    Route::get('/inquiries', MyInquiries::class)->name('inquiries');
    Route::get('/reviews', MyReviews::class)->name('reviews');
    Route::get('/profile', ProfileEdit::class)->name('profile');
});

// =============== POST ACTIONS (auth) ===============
Route::middleware('auth')->group(function () {
    Route::post('/schools/{school}/inquire', [\App\Http\Controllers\InquiryController::class, 'store'])->name('inquiry.store');
    Route::post('/schools/{school}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('review.store');
    Route::post('/schools/{school}/save', [\App\Http\Controllers\SavedSchoolController::class, 'toggle'])->name('school.save.toggle');
});

<?php

use App\Http\Controllers\Api\AdminWorkspaceController;
use App\Http\Controllers\Api\AdminFormController;
use App\Http\Controllers\Api\AdminTemplateController;
use App\Http\Controllers\Api\AdManagerController;
use App\Http\Controllers\Api\AdminLocationController;
use App\Http\Controllers\Api\DashboardAuthController;
use App\Http\Controllers\Api\OwnerWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/registration/options', [DashboardAuthController::class, 'registrationOptions'])->middleware('throttle:30,1');
Route::get('/branding/dashboard', [AdminTemplateController::class, 'publicDashboardBranding'])->middleware('throttle:120,1');

Route::prefix('auth')->group(function () {
    Route::post('/login', [DashboardAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', [DashboardAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/register-school', [DashboardAuthController::class, 'registerSchool'])->middleware('throttle:5,1');
    Route::post('/verify-admin-code', [DashboardAuthController::class, 'verifyAdminCode'])->middleware('throttle:10,1');
    Route::post('/accept-admin-invite', [AdminTemplateController::class, 'acceptInvitation'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [DashboardAuthController::class, 'me']);
        Route::post('/logout', [DashboardAuthController::class, 'logout']);
        Route::post('/handoff', [DashboardAuthController::class, 'handoff']);
    });
});

Route::middleware(['auth:sanctum'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/overview', [AdminWorkspaceController::class, 'overview']);
        Route::get('/ad-manager', [AdManagerController::class, 'index']);
        Route::post('/ad-campaigns', [AdManagerController::class, 'storeCampaign']);
        Route::post('/ad-campaigns/{campaign}/banners', [AdManagerController::class, 'storeBanner']);
        Route::patch('/ad-banners/{banner}', [AdManagerController::class, 'updateBanner']);
        Route::delete('/ad-banners/{banner}', [AdManagerController::class, 'deleteBanner']);
        Route::put('/ad-placements', [AdManagerController::class, 'updatePlacements']);

        // Keep the static import route before the dynamic {level} route below.
        Route::get('/location-manager', [AdminLocationController::class, 'index']);
        Route::post('/location-manager/import', [AdminLocationController::class, 'import']);
        Route::post('/location-manager/{level}', [AdminLocationController::class, 'store'])
            ->whereIn('level', ['regions', 'districts', 'towns']);
        Route::patch('/location-manager/{level}/{id}', [AdminLocationController::class, 'update'])
            ->whereIn('level', ['regions', 'districts', 'towns'])
            ->whereNumber('id');
        Route::delete('/location-manager/{level}/{id}', [AdminLocationController::class, 'destroy'])
            ->whereIn('level', ['regions', 'districts', 'towns'])
            ->whereNumber('id');
        Route::put('/location-manager/settings', [AdminLocationController::class, 'updateSettings']);
        Route::post('/location-suggestions/{suggestion}/review', [AdminLocationController::class, 'reviewSuggestion'])
            ->whereNumber('suggestion');

        Route::get('/form-fields', [AdminFormController::class, 'index']);
        Route::put('/form-fields/draft', [AdminFormController::class, 'saveDraft']);
        Route::post('/form-fields/publish', [AdminFormController::class, 'publish']);
        Route::get('/template-settings', [AdminTemplateController::class, 'settings']);
        Route::put('/template-settings', [AdminTemplateController::class, 'updateSettings']);
        Route::post('/media-upload', [AdminTemplateController::class, 'uploadMedia']);
        Route::get('/template-access', [AdminTemplateController::class, 'access']);
        Route::post('/template-access/roles', [AdminTemplateController::class, 'createRole']);
        Route::put('/template-access/roles/{role}', [AdminTemplateController::class, 'updateRole']);
        Route::post('/template-access/reset', [AdminTemplateController::class, 'resetRoles']);
        Route::post('/template-access/invitations', [AdminTemplateController::class, 'inviteAdmin']);
        Route::patch('/template-access/users/{user}', [AdminTemplateController::class, 'updateAdmin']);
        Route::post('/template-access/users/{user}/countries/{country}/approve', [AdminTemplateController::class, 'approveCountry']);
        Route::delete('/template-access/users/{user}', [AdminTemplateController::class, 'deleteAdmin']);
        Route::post('/template-access/sign-out-others', [AdminTemplateController::class, 'signOutOthers']);
        Route::get('/{resource}', [AdminWorkspaceController::class, 'index'])
            ->whereIn('resource', ['schools', 'verification', 'owners', 'parents', 'enquiries', 'reviews', 'articles', 'article-categories', 'events', 'scholarships', 'school-news', 'advertisements', 'settings', 'plans', 'subscriptions', 'payments', 'activity', 'contacts', 'locations']);
        Route::post('/articles', [AdminWorkspaceController::class, 'storeArticle']);
        Route::post('/advertisements', [AdminWorkspaceController::class, 'storeAdvertisement']);
        Route::patch('/{resource}/{id}', [AdminWorkspaceController::class, 'update'])
            ->whereIn('resource', ['schools', 'verification', 'owners', 'parents', 'enquiries', 'reviews', 'articles', 'events', 'scholarships', 'school-news', 'advertisements', 'settings', 'plans', 'contacts']);
    });

// Owners can manage only their own schools. Administrators can use the same
// school tools across the platform, but cannot edit another user's personal account.
Route::middleware(['auth:sanctum', 'role:admin|school_owner'])
    ->prefix('owner')
    ->group(function () {
        Route::get('/workspace', [OwnerWorkspaceController::class, 'workspace']);

        Route::get('/schools/{school}/profile', [OwnerWorkspaceController::class, 'profile']);
        Route::put('/schools/{school}/profile', [OwnerWorkspaceController::class, 'profile']);
        Route::get('/schools/{school}/admissions', [OwnerWorkspaceController::class, 'admissions']);
        Route::put('/schools/{school}/admissions', [OwnerWorkspaceController::class, 'admissions']);
        Route::get('/schools/{school}/facilities', [OwnerWorkspaceController::class, 'facilities']);
        Route::put('/schools/{school}/facilities', [OwnerWorkspaceController::class, 'facilities']);

        Route::get('/schools/{school}/programmes', [OwnerWorkspaceController::class, 'programmes']);
        Route::post('/schools/{school}/programmes', [OwnerWorkspaceController::class, 'programmes']);
        Route::delete('/schools/{school}/programmes/{programme}', [OwnerWorkspaceController::class, 'programmes']);

        Route::get('/schools/{school}/fees', [OwnerWorkspaceController::class, 'fees']);
        Route::post('/schools/{school}/fees', [OwnerWorkspaceController::class, 'fees']);
        Route::delete('/schools/{school}/fees/{fee}', [OwnerWorkspaceController::class, 'fees']);

        Route::get('/schools/{school}/media/{collection}', [OwnerWorkspaceController::class, 'media']);
        Route::post('/schools/{school}/media/{collection}', [OwnerWorkspaceController::class, 'media']);
        Route::delete('/schools/{school}/media/{collection}/{mediaId}', [OwnerWorkspaceController::class, 'media']);

        Route::get('/schools/{school}/videos', [OwnerWorkspaceController::class, 'videos']);
        Route::post('/schools/{school}/videos', [OwnerWorkspaceController::class, 'videos']);
        Route::delete('/schools/{school}/videos/{video}', [OwnerWorkspaceController::class, 'videos']);

        Route::get('/schools/{school}/content/{type}', [OwnerWorkspaceController::class, 'content']);
        Route::post('/schools/{school}/content/{type}', [OwnerWorkspaceController::class, 'content']);
        Route::delete('/schools/{school}/content/{type}/{post}', [OwnerWorkspaceController::class, 'content']);

        Route::get('/schools/{school}/advertisements', [OwnerWorkspaceController::class, 'advertisements']);
        Route::post('/schools/{school}/advertisements', [OwnerWorkspaceController::class, 'advertisements']);
        Route::delete('/schools/{school}/advertisements/{advertisement}', [OwnerWorkspaceController::class, 'advertisements']);

        Route::get('/schools/{school}/message-campaigns', [OwnerWorkspaceController::class, 'messageCampaigns']);
        Route::post('/schools/{school}/message-campaigns', [OwnerWorkspaceController::class, 'messageCampaigns']);
        Route::delete('/schools/{school}/message-campaigns/{campaign}', [OwnerWorkspaceController::class, 'messageCampaigns']);

        Route::get('/schools/{school}/subscription', [OwnerWorkspaceController::class, 'subscription']);
    });

Route::middleware(['auth:sanctum', 'role:school_owner'])
    ->prefix('owner')
    ->group(function () {
        Route::get('/account', [OwnerWorkspaceController::class, 'account']);
        Route::put('/account', [OwnerWorkspaceController::class, 'account']);
        Route::post('/account/password', [OwnerWorkspaceController::class, 'password']);
        Route::post('/account/deletion-request', [OwnerWorkspaceController::class, 'requestDeletion']);
    });

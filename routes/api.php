<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AdminPaymentController,
    AuthController,
    ProfileController,
    SocialLinkController,
    TemplateController,
    PaymentProofController,
    TemplateUnlockController,
    UserTemplateController,
    StatsController,
    PasswordResetController
};
use App\Models\Template;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// Public templates & stats
Route::get('/stats/top-templates', [StatsController::class, 'topTemplates']);
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{slug}', [TemplateController::class, 'show']);
Route::get('/templates/id/{id}', [TemplateController::class, 'showById']);
Route::get('/templates/check-slug/{slug}', [TemplateController::class, 'checkSlug']);

/*
|--------------------------------------------------------------------------
| Protected Routes (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [ProfileController::class, 'me']); // current user

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'me']); // alias
        Route::get('/user', [ProfileController::class, 'me']); // logged-in user's profile
        Route::post('/', [ProfileController::class, 'storeOrUpdate']);
        Route::post('/publish', [ProfileController::class, 'publish']);

        // Social links
        Route::post('/social-links', [SocialLinkController::class, 'store']);
        Route::put('/social-links/{id}', [SocialLinkController::class, 'update']);

        // User's templates
        Route::get('/user/used-templates', [UserTemplateController::class, 'usedTemplates']);
    });

    /*
    |--------------------------------------------------------------------------
    | User Template Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('templates')->group(function () {
        Route::get('/status', [UserTemplateController::class, 'templatesStatus']);
        Route::get('/saved', [UserTemplateController::class, 'onlySavedTemplates']);
        Route::post('/saved/{template}', [UserTemplateController::class, 'saveTemplate']);
        Route::delete('/saved/{template}', [UserTemplateController::class, 'unsaveTemplate']);

        Route::get('/used', [UserTemplateController::class, 'usedTemplates']);
        Route::post('/used/{slug}', [UserTemplateController::class, 'useTemplate']);
        Route::delete('/used/{slug}', [UserTemplateController::class, 'unuseTemplate']);

        Route::get('/{slug}/status', [UserTemplateController::class, 'showWithStatus']);
        Route::get('/', [UserTemplateController::class, 'userTemplatesWithStatus']);
        Route::get('/bought', [UserTemplateController::class, 'fetchBoughted']); // renamed for clarity
        Route::post('/payment/submit', [UserTemplateController::class, 'submit']);
    });

    /*
    |--------------------------------------------------------------------------
    | Template Unlocks
    |--------------------------------------------------------------------------
    */
    Route::get('/template-unlocks', [TemplateUnlockController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Payment Proofs
    |--------------------------------------------------------------------------
    */
    Route::prefix('payment-proofs')->group(function () {
        Route::post('/', [PaymentProofController::class, 'store']);
        Route::get('/', [PaymentProofController::class, 'index']); // admin only
        Route::post('/{id}/approve', [PaymentProofController::class, 'approve']);
        Route::post('/{id}/decline', [PaymentProofController::class, 'decline']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {
        // Users
        Route::get('/users', [AuthController::class, 'index']);
        Route::get('/user/{id}', [AuthController::class, 'getUserById']);

        // Payments
        Route::get('/payments', [AdminPaymentController::class, 'index']);
        Route::post('/payments/{id}/approve', [AdminPaymentController::class, 'approve']);
        Route::post('/payments/{id}/disapprove', [AdminPaymentController::class, 'disapprove']);
        Route::get('/payments/count', [AdminPaymentController::class, 'count']);
    });

    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */
    Route::get('/stats/templates-count', function () {
        $thisMonth = Template::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonth = Template::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        return response()->json([
            'count' => Template::count(),
            'change' => $thisMonth - $lastMonth
        ]);
    });

    Route::get('/stats/revenue', [StatsController::class, 'revenue']);
    Route::get('/stats/pending-payments', [StatsController::class, 'pendingPayments']);
    Route::get('/stats/user-growth', [StatsController::class, 'userGrowth']);
    Route::get('/stats/template-distribution', [StatsController::class, 'templateDistribution']);
});

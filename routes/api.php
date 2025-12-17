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

// Public stats
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
    Route::get('/user-profile', [ProfileController::class, 'me']); // Current logged-in user

    // Profile
    Route::get('/user', [ProfileController::class, 'me']);
    Route::post('/profile', [ProfileController::class, 'storeOrUpdate']);
    Route::post('/profile/publish', [ProfileController::class, 'publish']);

    // Alias for frontend convenience (for /profile/user)
    Route::get('/profile/user', [ProfileController::class, 'me']); // returns currently logged-in user's profile if logged in
Route::get('/profile/user/used-templates', [UserTemplateController::class, 'usedTemplates']); // returns logged-in user's used templates


    // Social links
    Route::post('/profile/social-links', [SocialLinkController::class, 'store']);
    Route::put('/profile/social-links/{id}', [SocialLinkController::class, 'update']);

    // User templates
    Route::get('/templates/status', [UserTemplateController::class, 'templatesStatus']);
    Route::get('/templates1/saved', [UserTemplateController::class, 'onlySavedTemplates']);
    Route::post('/templates/saved/{template}', [UserTemplateController::class, 'saveTemplate']);
    Route::delete('/templates/saved/{template}', [UserTemplateController::class, 'unsaveTemplate']);
    Route::get('/templates1/used', [UserTemplateController::class, 'usedTemplates']);
    Route::post('/templates/used/{slug}', [UserTemplateController::class, 'useTemplate']);
    Route::delete('/templates/used/{slug}', [UserTemplateController::class, 'unuseTemplate']);
    Route::get('/templates/{slug}/status', [UserTemplateController::class, 'showWithStatus']);
    Route::get('/templates2', [UserTemplateController::class, 'userTemplatesWithStatus']);
    Route::get('/templates1/boughted', [UserTemplateController::class, 'fetchBoughted']);
    Route::post('/payment/submit', [UserTemplateController::class, 'submit']);

    // Template unlocks
    Route::get('/template-unlocks', [TemplateUnlockController::class, 'index']);

    // Payments & proofs
    Route::post('/payment-proofs', [PaymentProofController::class, 'store']);
    Route::get('/payment-proofs', [PaymentProofController::class, 'index']); // Admin only
    Route::post('/payment-proofs/{id}/approve', [PaymentProofController::class, 'approve']);
    Route::post('/payment-proofs/{id}/decline', [PaymentProofController::class, 'decline']);

    // Admin routes
    Route::get('/admin/users', [AuthController::class, 'index']);
    Route::get('/admin/user/{id}', [AuthController::class, 'getUserById']);
    Route::get('/admin/payments', [AdminPaymentController::class, 'index']);
    Route::post('/admin/payments/{id}/approve', [AdminPaymentController::class, 'approve']);
    Route::post('/admin/payments/{id}/disapprove', [AdminPaymentController::class, 'disapprove']);
    Route::get('/admin/payments/count', [AdminPaymentController::class, 'count']);

    // Stats
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

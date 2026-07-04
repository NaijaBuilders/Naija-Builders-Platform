<?php

use App\Http\Controllers\Mobile\AccountController;
use App\Http\Controllers\Mobile\AdminBuyerReviewController;
use App\Http\Controllers\Mobile\AdminSupplierOnboardingController;
use App\Http\Controllers\Mobile\AddressController;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\BuyerKycController;
use App\Http\Controllers\Mobile\BuyerOtpController;
use App\Http\Controllers\Mobile\CartController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\DeliveryConfirmationController;
use App\Http\Controllers\Mobile\EmailVerificationController;
use App\Http\Controllers\Mobile\ListingController;
use App\Http\Controllers\Mobile\MaterialsController;
use App\Http\Controllers\Mobile\MessageController;
use App\Http\Controllers\Mobile\NotificationController;
use App\Http\Controllers\Mobile\NotificationPreferenceController;
use App\Http\Controllers\Mobile\OrderController;
use App\Http\Controllers\Mobile\PasswordResetController;
use App\Http\Controllers\Mobile\PremblyWebhookController;
use App\Http\Controllers\Mobile\ProfileController;
use App\Http\Controllers\Mobile\SavedMaterialController;
use App\Http\Controllers\Mobile\ServiceRequestController;
use App\Http\Controllers\Mobile\StaticPageController;
use App\Http\Controllers\Mobile\SubscriptionController;
use App\Http\Controllers\Mobile\SupplierOnboardingController;
use App\Http\Controllers\Mobile\SupportController;
use Illuminate\Support\Facades\Route;

Route::post('/kyc/prembly/webhook', PremblyWebhookController::class)
    ->name('kyc.prembly.webhook');

Route::prefix('mobile')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => 'naijabuilders-mobile-api',
    ]));

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:5,1');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,1');

    Route::get('/materials', [MaterialsController::class, 'index']);
    Route::get('/materials/{materialId}', [MaterialsController::class, 'show']);
    Route::get('/terms', [StaticPageController::class, 'terms']);
    Route::get('/support', [SupportController::class, 'index']);
    Route::get('/forgot-password', [StaticPageController::class, 'forgotPassword']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/analysis', [DashboardController::class, 'analysis']);
        Route::get('/dashboard/buyer', [DashboardController::class, 'buyer']);

        Route::post('/materials/{materialId}/review', [MaterialsController::class, 'rateProduct']);
        Route::post('/suppliers/{supplierId}/review', [MaterialsController::class, 'rateSupplier']);

        Route::get('/listings', [ListingController::class, 'index']);
        Route::post('/listings', [ListingController::class, 'store']);
        Route::delete('/listings/{listingId}', [ListingController::class, 'destroy']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'add']);
        Route::put('/cart/items/{materialId}', [CartController::class, 'update']);
        Route::delete('/cart/items/{materialId}', [CartController::class, 'remove']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{orderId}', [OrderController::class, 'show']);
        Route::post('/orders/{orderId}/delivery/photos', [DeliveryConfirmationController::class, 'uploadPhoto']);
        Route::post('/orders/{orderId}/delivery/otp', [DeliveryConfirmationController::class, 'generateOtp']);
        Route::post('/orders/{orderId}/delivery/otp/confirm', [DeliveryConfirmationController::class, 'confirmOtp']);
        Route::post('/orders/{orderId}/disputes', [DeliveryConfirmationController::class, 'dispute']);

        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);

        Route::get('/services/options', [ServiceRequestController::class, 'options']);
        Route::post('/services/requests', [ServiceRequestController::class, 'store']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::get('/profile/stats', [ProfileController::class, 'stats']);
        Route::get('/settings', [ProfileController::class, 'settings']);
        Route::post('/settings', [ProfileController::class, 'saveSettings']);

        Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index']);
        Route::post('/notification-preferences', [NotificationPreferenceController::class, 'update']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read', [NotificationController::class, 'markRead']);
        Route::post('/device-tokens', [NotificationController::class, 'registerDevice']);

        Route::delete('/account', [AccountController::class, 'destroy']);
        Route::post('/account/password', [AccountController::class, 'changePassword']);

        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{addressId}', [AddressController::class, 'update']);
        Route::delete('/addresses/{addressId}', [AddressController::class, 'destroy']);

        Route::post('/kyc/email-verification', EmailVerificationController::class);
        Route::post('/otp/send', [BuyerOtpController::class, 'send']);
        Route::post('/otp/confirm', [BuyerOtpController::class, 'confirm']);
        Route::post('/kyc/buyer/id-document', [BuyerKycController::class, 'store']);

        Route::get('/supplier/onboarding/status', [SupplierOnboardingController::class, 'status']);
        Route::get('/supplier/onboarding/applications/{application}', [SupplierOnboardingController::class, 'show']);
        Route::post('/supplier/onboarding/business-details', [SupplierOnboardingController::class, 'businessDetails']);
        Route::post('/supplier/onboarding/identity-verification', [SupplierOnboardingController::class, 'identity']);
        Route::post('/supplier/onboarding/bank-details', [SupplierOnboardingController::class, 'bankDetails']);
        Route::post('/supplier/onboarding/submit', [SupplierOnboardingController::class, 'submit']);

        Route::middleware('mobile.admin')->prefix('admin/supplier-onboarding')->group(function (): void {
            Route::get('/manual-review', [AdminSupplierOnboardingController::class, 'index']);
            Route::get('/{application}', [AdminSupplierOnboardingController::class, 'show']);
            Route::post('/{application}/approve', [AdminSupplierOnboardingController::class, 'approve']);
            Route::post('/{application}/reject', [AdminSupplierOnboardingController::class, 'reject']);
            Route::post('/{application}/more-info', [AdminSupplierOnboardingController::class, 'moreInfo']);
        });

        Route::middleware('mobile.admin')->prefix('admin/buyer-reviews')->group(function (): void {
            Route::get('/', [AdminBuyerReviewController::class, 'index']);
            Route::get('/{orderId}', [AdminBuyerReviewController::class, 'show']);
            Route::post('/{orderId}/approve', [AdminBuyerReviewController::class, 'approve']);
            Route::post('/{orderId}/reject', [AdminBuyerReviewController::class, 'reject']);
            Route::post('/{orderId}/more-info', [AdminBuyerReviewController::class, 'moreInfo']);
        });

        Route::get('/saved-products', [SavedMaterialController::class, 'index']);
        Route::post('/saved-products', [SavedMaterialController::class, 'store']);
        Route::delete('/saved-products/{materialId}', [SavedMaterialController::class, 'destroy']);

        Route::get('/subscription', [SubscriptionController::class, 'show']);
        Route::post('/subscription', [SubscriptionController::class, 'store']);
    });
});

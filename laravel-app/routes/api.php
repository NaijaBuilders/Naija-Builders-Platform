<?php

use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\CartController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\ListingController;
use App\Http\Controllers\Mobile\MaterialsController;
use App\Http\Controllers\Mobile\MessageController;
use App\Http\Controllers\Mobile\OrderController;
use App\Http\Controllers\Mobile\ProfileController;
use App\Http\Controllers\Mobile\SavedMaterialController;
use App\Http\Controllers\Mobile\StaticPageController;
use App\Http\Controllers\Mobile\SubscriptionController;
use App\Http\Controllers\Mobile\SupportController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => 'naijabuilders-mobile-api',
    ]));

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

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
        Route::get('/orders/{orderId}', [OrderController::class, 'show']);

        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::get('/settings', [ProfileController::class, 'settings']);
        Route::post('/settings', [ProfileController::class, 'saveSettings']);

        Route::get('/saved-products', [SavedMaterialController::class, 'index']);
        Route::post('/saved-products', [SavedMaterialController::class, 'store']);
        Route::delete('/saved-products/{materialId}', [SavedMaterialController::class, 'destroy']);

        Route::get('/subscription', [SubscriptionController::class, 'show']);
        Route::post('/subscription', [SubscriptionController::class, 'store']);
    });
});

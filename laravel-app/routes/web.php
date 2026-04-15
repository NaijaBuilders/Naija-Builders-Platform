<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedMaterialController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/index.php', [HomeController::class, 'index'])->name('home');

Route::get('/login.php', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/login.php', [AuthController::class, 'login'])->name('auth.login');
Route::get('/signup.php', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/auth/register.php', [AuthController::class, 'register'])->name('auth.register');
Route::get('/logout.php', [AuthController::class, 'logout'])->name('logout');
Route::get('/supplier-kyc.php', [AuthController::class, 'showSupplierKyc'])->middleware('legacy.auth')->name('supplier.kyc.show');
Route::post('/supplier-kyc.php', [AuthController::class, 'submitSupplierKyc'])->middleware('legacy.auth')->name('supplier.kyc.submit');

Route::get('/materials.php', [MaterialsController::class, 'index'])->name('materials.index');
Route::get('/material-detail.php', [MaterialsController::class, 'show'])->name('materials.show');
Route::get('/cart.php', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add.php', [CartController::class, 'add'])->middleware('legacy.auth')->name('cart.add');
Route::post('/cart/update.php', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove.php', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('legacy.auth')->group(function (): void {
    Route::get('/dashboard.php', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analysis.php', [DashboardController::class, 'analysis'])->name('dashboard.analysis');
    Route::get('/buyer-dashboard.php', [DashboardController::class, 'buyer'])->name('dashboard.buyer');
    Route::get('/create-listing.php', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/create-listing.php', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/manage-listings.php', [ListingController::class, 'index'])->name('listings.index');
    Route::post('/manage-listings.php', [ListingController::class, 'destroy'])->name('listings.destroy');

    Route::get('/messages.php', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages.php', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/edit-profile.php', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/api/update-profile.php', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings.php', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/settings.php', [ProfileController::class, 'saveSettings'])->name('profile.settings.save');

    Route::post('/materials/review-product.php', [MaterialsController::class, 'rateProduct'])->name('materials.review-product');
    Route::post('/materials/review-supplier.php', [MaterialsController::class, 'rateSupplier'])->name('materials.review-supplier');
});

Route::get('/forgot-password.php', [StaticPageController::class, 'forgotPassword'])->name('forgot-password');
Route::get('/terms-and-agreement.php', [StaticPageController::class, 'termsAndAgreement'])->name('terms-and-agreement');
Route::post('/terms-and-agreement/accept.php', [StaticPageController::class, 'acceptTermsAndAgreement'])->name('terms-and-agreement.accept');
Route::get('/support.php', [StaticPageController::class, 'support'])->name('support.show');

Route::middleware('legacy.auth')->group(function (): void {
    Route::get('/saved-products', [SavedMaterialController::class, 'index'])->name('saved-products.home');
    Route::get('/saved-products.php', [SavedMaterialController::class, 'index'])->name('saved-products.index');
    Route::post('/saved-products', [SavedMaterialController::class, 'store'])->name('saved-products.store-clean');
    Route::post('/saved-products.php', [SavedMaterialController::class, 'store'])->name('saved-products.store');
    Route::post('/saved-products/remove', [SavedMaterialController::class, 'destroy'])->name('saved-products.destroy-clean');
    Route::post('/saved-products/remove.php', [SavedMaterialController::class, 'destroy'])->name('saved-products.destroy');

    Route::get('/subscription.php', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/subscription.php', [SubscriptionController::class, 'store'])->name('subscription.store');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Admin\SellerVerificationController;
use App\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/register/customer', [RegisteredUserController::class, 'registerCustomer']);
Route::post('/register/seller', [RegisteredUserController::class, 'registerSeller']);
Route::post('/register/admin', [RegisteredUserController::class, 'registerAdmin']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Public product routes (customer browsing — no auth required)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/docs', function () {
    return view('swagger');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [PasswordController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{position}', [CartController::class, 'update']);
    Route::delete('/cart/{position}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // User Address routes
    Route::get('/user/addresses', [\App\Http\Controllers\CustomerAddressController::class, 'index']);
    Route::post('/user/addresses', [\App\Http\Controllers\CustomerAddressController::class, 'store']);
    Route::put('/user/addresses/{id}', [\App\Http\Controllers\CustomerAddressController::class, 'update']);
    Route::delete('/user/addresses/{id}', [\App\Http\Controllers\CustomerAddressController::class, 'destroy']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    // Order History routes
    Route::get('/user/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/user/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show']);

    // Checkout routes
    Route::prefix('checkout')->group(function () {
        Route::post('/address', [\App\Http\Controllers\CheckoutController::class, 'saveAddress']);
        Route::get('/shipping', [\App\Http\Controllers\CheckoutController::class, 'getShippingOptions']);
        Route::post('/shipping', [\App\Http\Controllers\CheckoutController::class, 'saveShipping']);
        Route::get('/payment', [\App\Http\Controllers\CheckoutController::class, 'getPaymentOptions']);
        Route::post('/payment', [\App\Http\Controllers\CheckoutController::class, 'savePayment']);
        Route::post('/process', [\App\Http\Controllers\CheckoutController::class, 'processOrder']);
    });

    // Seller management routes
    Route::middleware('seller')->prefix('seller')->group(function () {
        Route::post('/reupload-ktp', [SellerProfileController::class, 'reuploadKtp']);
        
        // Seller product management routes (must be approved)
        Route::middleware('seller.approved')->group(function () {
            Route::get('/products', [SellerProductController::class, 'index']);
            Route::post('/products', [SellerProductController::class, 'store']);
            Route::get('/products/{id}', [SellerProductController::class, 'show']);
            Route::patch('/products/{id}', [SellerProductController::class, 'update']);
            Route::delete('/products/{id}', [SellerProductController::class, 'destroy']);
        });
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/sellers/pending', [SellerVerificationController::class, 'index']);
        Route::post('/sellers/{id}/approve', [SellerVerificationController::class, 'approve']);
        Route::post('/sellers/{id}/reject', [SellerVerificationController::class, 'reject']);
    });
});


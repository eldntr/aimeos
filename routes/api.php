<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\SellerProfileController;
use App\Http\Controllers\Seller\CategoryController;
use App\Http\Controllers\Admin\SellerVerificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController as PublicCategoryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\RajaOngkirLocationController;
use App\Http\Controllers\Seller\VoucherController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\WalletController;
use App\Http\Controllers\Seller\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| 1. Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register');
Route::post('/register/customer', [RegisteredUserController::class, 'registerCustomer'])->middleware('throttle:register');
Route::post('/register/seller', [RegisteredUserController::class, 'registerSeller'])->middleware('throttle:register');
Route::post('/register/admin', [RegisteredUserController::class, 'registerAdmin'])->middleware('throttle:register');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

/*
|--------------------------------------------------------------------------
| 2. Public Marketplace Routes (Browsing & Search)
|--------------------------------------------------------------------------
*/
Route::get('/banners', [HomeController::class, 'getBanners']);
Route::get('/categories', [PublicCategoryController::class, 'index']);
Route::get('/categories/{id}/products', [PublicCategoryController::class, 'getProducts']);
Route::get('/shops/{shop_id}', [ShopController::class, 'show']);
Route::get('/shops/{shop_id}/products', [ShopController::class, 'getProducts']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/{id}/variants', [ProductController::class, 'getVariants']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
Route::get('/search/suggestions', [ProductController::class, 'suggest']);

// API Interactive Documentation (Swagger)
Route::get('/docs', function () {
    return view('swagger');
});

/*
|--------------------------------------------------------------------------
| 3. Protected Application Routes (Sanctum Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Basic Account Management
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [PasswordController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Shipping Locations (RajaOngkir integration)
    Route::prefix('rajaongkir/locations')->group(function () {
        Route::get('/provinces', [RajaOngkirLocationController::class, 'provinces']);
        Route::get('/cities', [RajaOngkirLocationController::class, 'cities']);
        Route::get('/subdistricts', [RajaOngkirLocationController::class, 'subdistricts']);
        Route::get('/komerce-destinations', [RajaOngkirLocationController::class, 'komerceDestinations']);
        Route::get('/couriers', [RajaOngkirLocationController::class, 'couriers']);
    });

    // In-app Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);

    // Shopping Cart Management
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{position}', [CartController::class, 'update']);
    Route::delete('/cart/{position}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/apply-voucher', [CartController::class, 'applyVoucher']);
    Route::delete('/cart/remove-voucher', [CartController::class, 'removeVoucher']);

    // Customer Addresses
    Route::get('/user/addresses', [\App\Http\Controllers\CustomerAddressController::class, 'index']);
    Route::post('/user/addresses', [\App\Http\Controllers\CustomerAddressController::class, 'store']);
    Route::put('/user/addresses/{id}', [\App\Http\Controllers\CustomerAddressController::class, 'update']);
    Route::delete('/user/addresses/{id}', [\App\Http\Controllers\CustomerAddressController::class, 'destroy']);

    // Customer Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    // Product Review Creation
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);

    // Customer Order History
    Route::get('/user/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/user/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show']);
    Route::patch('/user/orders/{id}/received', [\App\Http\Controllers\OrderController::class, 'markReceived']);

    // Order Resolution & Complaints
    Route::post('/orders/{id}/complaint', [ComplaintController::class, 'store']);
    Route::patch('/orders/{id}/resolution', [ComplaintController::class, 'resolve']);

    // Checkout Flow
    Route::prefix('checkout')->group(function () {
        Route::post('/address', [\App\Http\Controllers\CheckoutController::class, 'saveAddress']);
        Route::get('/shipping', [\App\Http\Controllers\CheckoutController::class, 'getShippingOptions']);
        Route::post('/shipping', [\App\Http\Controllers\CheckoutController::class, 'saveShipping']);
        Route::get('/payment', [\App\Http\Controllers\CheckoutController::class, 'getPaymentOptions']);
        Route::post('/payment', [\App\Http\Controllers\CheckoutController::class, 'savePayment']);
        Route::post('/process', [\App\Http\Controllers\CheckoutController::class, 'processOrder'])->middleware('throttle:checkout_process');
    });

    /*
    |----------------------------------------------------------------------
    | 3.1. Seller (Merchant) Space
    |----------------------------------------------------------------------
    */
    Route::middleware('seller')->prefix('seller')->group(function () {
        Route::post('/reupload-ktp', [SellerProfileController::class, 'reuploadKtp']);
        
        // Seller product management (Only accessible after seller account approval)
        Route::middleware('seller.approved')->group(function () {
            
            // Merchant Orders
            Route::get('/orders', [SellerOrderController::class, 'index']);
            Route::get('/orders/{id}', [SellerOrderController::class, 'show']);
            Route::patch('/orders/{id}/status', [SellerOrderController::class, 'updateStatus']);
            Route::post('/orders/{id}/complaint-response', [SellerOrderController::class, 'respondComplaint']);
            Route::post('/orders/{id}/pickup', [SellerOrderController::class, 'requestPickup']);

            // Merchant Shop & Bank settings
            Route::get('/shop', [SellerProfileController::class, 'getShop']);
            Route::put('/shop', [SellerProfileController::class, 'updateShop']);
            Route::put('/bank', [SellerProfileController::class, 'updateBank']);

            // Merchant Products & Variants
            Route::get('/products', [SellerProductController::class, 'index']);
            Route::post('/products', [SellerProductController::class, 'store']);
            Route::get('/products/{id}', [SellerProductController::class, 'show']);
            Route::patch('/products/{id}', [SellerProductController::class, 'update']);
            Route::delete('/products/{id}', [SellerProductController::class, 'destroy']);
            
            Route::get('/products/{id}/variants', [SellerProductController::class, 'getVariants']);
            Route::post('/products/{id}/variants', [SellerProductController::class, 'addVariant']);
            Route::delete('/products/{id}/variants/{variant_id}', [SellerProductController::class, 'deleteVariant']);

            // Merchant Product Images
            Route::get('/products/{id}/images', [SellerProductController::class, 'getImages']);
            Route::post('/products/{id}/images', [SellerProductController::class, 'uploadImages']);
            Route::delete('/products/{id}/images/{image_id}', [SellerProductController::class, 'deleteImage']);

            // Merchant Custom Categories
            Route::get('/categories', [CategoryController::class, 'index']);
            Route::post('/categories', [CategoryController::class, 'store']);

            // Merchant Vouchers
            Route::get('/vouchers', [VoucherController::class, 'index']);
            Route::post('/vouchers', [VoucherController::class, 'store']);

            // Merchant Wallet, Withdrawals & Sales Reports
            Route::get('/wallet', [WalletController::class, 'getWallet']);
            Route::post('/withdraw', [WalletController::class, 'withdraw']);
            Route::get('/wallet/ledger', [WalletController::class, 'getLedger']);
            Route::get('/reports/sales', [ReportController::class, 'getSales']);
            Route::get('/reports/export', [ReportController::class, 'export']);
        });
    });

    /*
    |----------------------------------------------------------------------
    | 3.2. Administrator Space
    |----------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Stats & Global User List
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats']);
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::patch('/users/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus']);
        
        // System Categories
        Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store']);
        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy']);
        
        // System Banners
        Route::get('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'index']);
        Route::post('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'store']);
        Route::delete('/banners/{id}', [\App\Http\Controllers\Admin\BannerController::class, 'destroy']);
        
        // Global Withdrawals Management
        Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index']);
        Route::patch('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve']);
        
        // Global Reports Export
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export']);
        
        // Global Notifications Broadcaster
        Route::post('/notifications/broadcast', [\App\Http\Controllers\Admin\NotificationController::class, 'broadcast']);
        
        // Seller Verification & KYC (Know Your Customer)
        Route::get('/sellers/pending', [SellerVerificationController::class, 'index']);
        Route::get('/sellers/active', [SellerVerificationController::class, 'activeSellers']);
        Route::patch('/sellers/{id}/status', [SellerVerificationController::class, 'toggleSellerStatus']);
        Route::post('/sellers/{id}/approve', [SellerVerificationController::class, 'approve']);
        Route::post('/sellers/{id}/reject', [SellerVerificationController::class, 'reject']);

        // Product Moderation
        Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index']);
        Route::post('/products/{id}/ban', [\App\Http\Controllers\Admin\ProductController::class, 'ban']);
        Route::delete('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy']);

        // System Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'store']);

        // Dispute & Order Moderation
        Route::get('/disputes', [\App\Http\Controllers\Admin\ComplaintModerationController::class, 'getDisputes']);
        Route::post('/disputes/{id}/resolve', [\App\Http\Controllers\Admin\ComplaintModerationController::class, 'resolveDispute']);
        Route::get('/orders', [\App\Http\Controllers\Admin\ComplaintModerationController::class, 'getOrders']);

        // Product Review Moderation
        Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'index']);
        Route::patch('/reviews/{id}/status', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'updateStatus']);

        // User Reports (Support Tickets)
        Route::get('/user-reports', [\App\Http\Controllers\Admin\UserReportModerationController::class, 'index']);
        Route::post('/user-reports/{id}/reply', [\App\Http\Controllers\Admin\UserReportModerationController::class, 'reply']);
    });
});

/*
|--------------------------------------------------------------------------
| 4. Client-side Javascript Error Logger
|--------------------------------------------------------------------------
*/
Route::post('/log-error', function (\Illuminate\Http\Request $request) { 
    \Illuminate\Support\Facades\Log::error('JS ERROR: ' . json_encode($request->all())); 
    return response()->json(['status' => 'ok']); 
});

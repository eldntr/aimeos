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

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{position}', [CartController::class, 'update']);
    Route::delete('/cart/{position}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/apply-voucher', [CartController::class, 'applyVoucher']);
    Route::delete('/cart/remove-voucher', [CartController::class, 'removeVoucher']);

    // User Address routes
    Route::get('/user/addresses', [\App\Http\Controllers\CustomerAddressController::class, 'index']);
    Route::post('/user/addresses', [\App\Http\Controllers\CustomerAddressController::class, 'store']);
    Route::put('/user/addresses/{id}', [\App\Http\Controllers\CustomerAddressController::class, 'update']);
    Route::delete('/user/addresses/{id}', [\App\Http\Controllers\CustomerAddressController::class, 'destroy']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    // Review routes
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);

    // Order History routes
    Route::get('/user/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/user/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show']);

    // Order Complaint & Resolution
    Route::post('/orders/{id}/complaint', [ComplaintController::class, 'store']);
    Route::patch('/orders/{id}/resolution', [ComplaintController::class, 'resolve']);

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
            Route::get('/orders', [SellerOrderController::class, 'index']);
            Route::get('/orders/{id}', [SellerOrderController::class, 'show']);
            Route::patch('/orders/{id}/status', [SellerOrderController::class, 'updateStatus']);
            Route::post('/orders/{id}/pickup', [SellerOrderController::class, 'requestPickup']);

            Route::get('/shop', [SellerProfileController::class, 'getShop']);
            Route::put('/shop', [SellerProfileController::class, 'updateShop']);
            Route::put('/bank', [SellerProfileController::class, 'updateBank']);

            Route::get('/products', [SellerProductController::class, 'index']);
            Route::post('/products', [SellerProductController::class, 'store']);
            Route::get('/products/{id}', [SellerProductController::class, 'show']);
            Route::patch('/products/{id}', [SellerProductController::class, 'update']);
            Route::delete('/products/{id}', [SellerProductController::class, 'destroy']);
            
            Route::get('/products/{id}/variants', [SellerProductController::class, 'getVariants']);
            Route::post('/products/{id}/variants', [SellerProductController::class, 'addVariant']);
            Route::delete('/products/{id}/variants/{variant_id}', [SellerProductController::class, 'deleteVariant']);

            Route::get('/products/{id}/images', [SellerProductController::class, 'getImages']);
            Route::post('/products/{id}/images', [SellerProductController::class, 'uploadImages']);
            Route::delete('/products/{id}/images/{image_id}', [SellerProductController::class, 'deleteImage']);

            // Seller category management routes
            Route::get('/categories', [CategoryController::class, 'index']);
            Route::post('/categories', [CategoryController::class, 'store']);

            // Seller voucher management routes
            Route::get('/vouchers', [VoucherController::class, 'index']);
            Route::post('/vouchers', [VoucherController::class, 'store']);

            // Seller wallet & reports
            Route::get('/wallet', [WalletController::class, 'getWallet']);
            Route::post('/withdraw', [WalletController::class, 'withdraw']);
            Route::get('/wallet/ledger', [WalletController::class, 'getLedger']);
            Route::get('/reports/sales', [ReportController::class, 'getSales']);
            Route::get('/reports/export', [ReportController::class, 'export']);
        });
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats']);
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::patch('/users/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus']);
        
        Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store']);
        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy']);
        
        Route::get('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'index']);
        Route::post('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'store']);
        Route::delete('/banners/{id}', [\App\Http\Controllers\Admin\BannerController::class, 'destroy']);
        
        Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index']);
        Route::patch('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve']);
        
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export']);
        
        Route::post('/notifications/broadcast', [\App\Http\Controllers\Admin\NotificationController::class, 'broadcast']);
        
        Route::get('/sellers/pending', [SellerVerificationController::class, 'index']);
        Route::get('/sellers/active', [SellerVerificationController::class, 'activeSellers']);
        Route::patch('/sellers/{id}/status', [SellerVerificationController::class, 'toggleSellerStatus']);
        Route::post('/sellers/{id}/approve', [SellerVerificationController::class, 'approve']);
        Route::post('/sellers/{id}/reject', [SellerVerificationController::class, 'reject']);

        Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index']);
        Route::post('/products/{id}/ban', [\App\Http\Controllers\Admin\ProductController::class, 'ban']);
        Route::delete('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy']);

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'store']);

        Route::get('/disputes', [\App\Http\Controllers\Admin\ComplaintModerationController::class, 'getDisputes']);
        Route::post('/disputes/{id}/resolve', [\App\Http\Controllers\Admin\ComplaintModerationController::class, 'resolveDispute']);
        Route::get('/orders', [\App\Http\Controllers\Admin\ComplaintModerationController::class, 'getOrders']);

        // Review/Ulasan Moderation Routes
        Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'index']);
        Route::patch('/reviews/{id}/status', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'updateStatus']);

        // User Reports Support Tickets Moderation Routes
        Route::get('/user-reports', [\App\Http\Controllers\Admin\UserReportModerationController::class, 'index']);
        Route::post('/user-reports/{id}/reply', [\App\Http\Controllers\Admin\UserReportModerationController::class, 'reply']);
    });
});

Route::post('/log-error', function (\Illuminate\Http\Request $request) { \Illuminate\Support\Facades\Log::error('JS ERROR: ' . json_encode($request->all())); return response()->json(['status' => 'ok']); });

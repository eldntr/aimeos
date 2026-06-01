<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Web\MarketplaceController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\MerchantController;

Route::get('/ready', function() {
    return 'OK';
});

Route::get('/', [MarketplaceController::class, 'landing'])->name('landing');
Route::get('/categories', [MarketplaceController::class, 'categories'])->name('categories');
Route::get('/categories/{selected_category}', [MarketplaceController::class, 'showCategory'])->name('categories.show');
Route::get('/products/{id}', [MarketplaceController::class, 'productDetail'])->name('products.show');
Route::get('/shops/{shop_code}', [MarketplaceController::class, 'shopDetail'])->name('shops.show');

Route::view('/welcome', 'pages.welcome')->name('welcome');
Route::view('/design-system-demo', 'pages.design-system-demo')->name('design-system-demo');
Route::view('/info', 'pages.marketplace.info')->name('marketplace.info');
// Cart: primary name 'marketplace.cart', alias 'cart.index'
Route::get('/cart', function() { return view('pages.marketplace.cart'); })->name('marketplace.cart');
// Checkout: primary name 'marketplace.checkout', alias 'checkout.index'
Route::get('/checkout', function() { return view('pages.marketplace.checkout'); })->name('marketplace.checkout');
// Route name aliases using URL aliases (prefix-free redirects so navigation route() calls work)
Route::get('/cart-redirect', fn() => redirect('/cart'))->name('cart.index');
Route::get('/checkout-redirect', fn() => redirect('/checkout'))->name('checkout.index');
Route::view('/merchant/register', 'auth.merchant-register')->name('merchant.register');
Route::post('/merchant/register', [RegisteredUserController::class, 'registerSeller'])->name('merchant.register.store');

Route::view('/tentang-kami', 'pages.static.about')->name('tentang-kami');
Route::view('/cara-kerja', 'pages.static.how-it-works')->name('cara-kerja');
Route::view('/karir', 'pages.static.career')->name('karir');
Route::view('/help-center', 'pages.static.help-center')->name('help-center');
Route::view('/keamanan', 'pages.static.security')->name('keamanan');
Route::view('/syarat-ketentuan', 'pages.static.terms')->name('syarat-ketentuan');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('/admin/dashboard', 'pages.admin.dashboard')->name('admin.dashboard');
    Route::view('/admin/settings', 'pages.admin.settings.index')->name('admin.settings.index');
    Route::get('/admin/merchants', [AdminController::class, 'merchantsIndex'])->name('admin.merchants.index');
    Route::get('/admin/merchants/{merchant}', [AdminController::class, 'merchantShow'])->name('admin.merchants.show');
    Route::post('/admin/merchants/{merchant}/approve', [AdminController::class, 'approve'])->name('admin.merchants.approve');
    Route::post('/admin/merchants/{merchant}/reject', [AdminController::class, 'reject'])->name('admin.merchants.reject');

    Route::get('/merchant/dashboard', [MerchantController::class, 'dashboard'])->name('merchant.dashboard');
    Route::view('/merchant/shop', 'pages.merchant.shop')->name('merchant.shop');
    Route::get('/merchant/products', [MerchantController::class, 'index'])->name('merchant.products.index');
    Route::get('/merchant/products/create', [MerchantController::class, 'create'])->name('merchant.products.create');
    Route::get('/merchant/products/{product}/edit', [MerchantController::class, 'edit'])->name('merchant.products.edit');
    Route::view('/merchant/products/media-manager', 'pages.merchant.products.media-manager')->name('merchant.products.media-manager');
    Route::post('/merchant/products', [MerchantController::class, 'store'])->name('merchant.products.store');
    Route::put('/merchant/products/{product}', [MerchantController::class, 'update'])->name('merchant.products.update');
    Route::delete('/merchant/products/{product}', [MerchantController::class, 'destroy'])->name('merchant.products.destroy');

    // Merchant Orders
    Route::get('/merchant/orders', [MerchantController::class, 'ordersIndex'])->name('merchant.orders.index');
    Route::get('/merchant/orders/{id}', [MerchantController::class, 'ordersShow'])->name('merchant.orders.show');
    Route::post('/merchant/orders/{id}/status', [MerchantController::class, 'ordersUpdateStatus'])->name('merchant.orders.update-status');
    Route::post('/merchant/orders/{id}/pickup', [MerchantController::class, 'ordersRequestPickup'])->name('merchant.orders.request-pickup');

    // Merchant Wallet / Earnings
    Route::get('/merchant/wallet', [MerchantController::class, 'walletIndex'])->name('merchant.wallet.index');
    Route::post('/merchant/wallet/withdraw', [MerchantController::class, 'walletWithdraw'])->name('merchant.wallet.withdraw');

    // Merchant Vouchers
    Route::get('/merchant/vouchers', [MerchantController::class, 'vouchersIndex'])->name('merchant.vouchers.index');
    Route::get('/merchant/vouchers/create', [MerchantController::class, 'vouchersCreate'])->name('merchant.vouchers.create');
    Route::post('/merchant/vouchers', [MerchantController::class, 'vouchersStore'])->name('merchant.vouchers.store');

    // Merchant Product Variants (AJAX)
    Route::post('/merchant/products/{product}/variants', [MerchantController::class, 'addVariantAJAX'])->name('merchant.products.variants.store');
    Route::delete('/merchant/products/{product}/variants/{variant_id}', [MerchantController::class, 'deleteVariantAJAX'])->name('merchant.products.variants.destroy');
});

$params = [];
$conf = ['prefix' => '', 'where' => []];

if( env( 'SHOP_MULTILOCALE' ) )
{
    $conf['prefix'] .= '{locale}';
    $conf['where']['locale'] = '[a-z]{2}(\_[A-Z]{2})?';
    $params = ['locale' => app()->getLocale()];
}

if( env( 'SHOP_MULTISHOP' ) )
{
    $conf['prefix'] .= '/{site}';
    $conf['where']['site'] = '^(?!profile|login|register|logout|dashboard|forgot-password|reset-password|verify-email|confirm-password|ready)[A-Za-z0-9\.\-]+';
}

if( $conf['prefix'] )
{
    Route::get('/', function() use ($params) {
        return redirect(airoute('aimeos_home', $params));
    });
}

Route::group($conf ?? [], function() {
    require __DIR__.'/auth.php';
});

Route::group(['middleware' => ['web']], function() {
    require __DIR__.'/auth.php';
});

if( env( 'SHOP_MULTIROUTE' ) )
{
    Route::group( $conf + ['middleware' => ['web']], function() {
        Route::match( ['GET', 'POST'], '/{path?}', array(
            'as' => 'aimeos_resolve',
            'uses' => 'Aimeos\Shop\Controller\ResolveController@indexAction'
        ) )->where( ['locale' => '[a-z]{2}(\_[A-Z]{2})?', 'site' => '^(?!profile|login|register|logout|dashboard|forgot-password|reset-password|verify-email|confirm-password|ready)[A-Za-z0-9\.\-]+'], 'path', '.*' );
    });
}
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
use App\Http\Controllers\Admin\ChatKeywordController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\HelpController;

Route::get('/ready', function() {
    return 'OK';
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/', [MarketplaceController::class, 'landing'])->name('landing');
Route::get('/categories', [MarketplaceController::class, 'categories'])->name('categories');
Route::get('/categories/{selected_category}', [MarketplaceController::class, 'showCategory'])->name('categories.show');
Route::get('/products/{id}', [MarketplaceController::class, 'productDetail'])->name('products.show');
Route::get('/shops/{shop_code}', [MarketplaceController::class, 'shopDetail'])->name('shops.show');
Route::get('/marketplace/search', [MarketplaceController::class, 'search'])->name('marketplace.search');

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
Route::get('/help-center', [HelpController::class, 'index'])->name('help-center');
Route::post('/help-center/report', [HelpController::class, 'storeReport'])->name('help-center.report');
Route::view('/keamanan', 'pages.static.security')->name('keamanan');
Route::view('/syarat-ketentuan', 'pages.static.terms')->name('syarat-ketentuan');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('/admin/dashboard', 'pages.admin.dashboard')->name('admin.dashboard');
    Route::view('/admin/settings', 'pages.admin.settings.index')->name('admin.settings.index');
    Route::get('/admin/merchants', [AdminController::class, 'merchantsIndex'])->name('admin.merchants.index');
    Route::get('/admin/merchants/{merchant}', [AdminController::class, 'merchantShow'])->name('admin.merchants.show');
    Route::post('/admin/merchants/{merchant}/approve', [AdminController::class, 'approve'])->name('admin.merchants.approve');
    Route::post('/admin/merchants/{merchant}/reject', [AdminController::class, 'reject'])->name('admin.merchants.reject');

    // Admin: Chat Blocked Keywords
    Route::get('/admin/chat-keywords', [ChatKeywordController::class, 'index'])->name('admin.chat-keywords.index');
    Route::post('/admin/chat-keywords', [ChatKeywordController::class, 'store'])->name('admin.chat-keywords.store');
    Route::patch('/admin/chat-keywords/{keyword}/toggle', [ChatKeywordController::class, 'toggle'])->name('admin.chat-keywords.toggle');
    Route::delete('/admin/chat-keywords/{keyword}', [ChatKeywordController::class, 'destroy'])->name('admin.chat-keywords.destroy');

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

    Route::post('/merchant/products/{product}/variants', [MerchantController::class, 'addVariantAJAX'])->name('merchant.products.variants.store');
    Route::delete('/merchant/products/{product}/variants/{variant_id}', [MerchantController::class, 'deleteVariantAJAX'])->name('merchant.products.variants.destroy');

    // Merchant Product Images (AJAX)
    Route::delete('/merchant/products/{product}/images/{image_id}', [MerchantController::class, 'deleteImageAJAX'])->name('merchant.products.images.destroy');
    Route::post('/merchant/products/{product}/images/reorder', [MerchantController::class, 'reorderImagesAJAX'])->name('merchant.products.images.reorder');


    // Custom Marketplace Chat
    Route::get('/marketplace/chat/messages', [\App\Http\Controllers\MarketplaceChatController::class, 'getMessages']);
    Route::post('/marketplace/chat/messages', [\App\Http\Controllers\MarketplaceChatController::class, 'sendMessage'])->middleware('block.contact.info');
    Route::get('/marketplace/chat/conversations', [\App\Http\Controllers\MarketplaceChatController::class, 'getConversations']);
    Route::post('/marketplace/chat/read', [\App\Http\Controllers\MarketplaceChatController::class, 'markAsRead']);
    Route::get('/marketplace/chat', function () {
        return view('pages.marketplace.chat');
    })->name('marketplace.chat');
    Route::redirect('/chatify', '/marketplace/chat');

    // Custom Profile Pages
    Route::get('/profile/orders', function () {
        return view('pages.profile.orders');
    })->name('profile.orders');
    Route::get('/profile/wishlist', function () {
        return view('pages.profile.wishlist');
    })->name('profile.wishlist');
    Route::get('/profile/vouchers', function () {
        return view('pages.profile.vouchers');
    })->name('profile.vouchers');
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
}Route::get('/log-error', function (\Illuminate\Http\Request $request) { \Illuminate\Support\Facades\Log::error('JS ERROR: ' . $request->get('msg')); return response()->json(['status' => 'ok']); });

Route::get('/dev/otp', function (\Illuminate\Http\Request $request) {
    if (config('app.env') !== 'local' && config('app.env') !== 'testing') {
        abort(403, 'This endpoint is only available in local development mode.');
    }

    $email = $request->query('email');
    $num = $request->query('num');

    if (!$email && !$num) {
        return response('Silakan masukkan parameter ?email=... atau ?num=... (nomor telepon).', 400);
    }

    $user = null;
    if ($email) {
        $user = \App\Models\User::where('email', $email)->first();
    } elseif ($num) {
        $user = \App\Models\User::where('telephone', $num)->first();
    }

    if (!$user) {
        return response('User tidak ditemukan dengan email atau nomor telepon tersebut.', 404);
    }

    // 1. Generate standard temporary signed verification URL on the fly
    $time = \Illuminate\Support\Carbon::now()->addMinutes(config('auth.verification.expire', 60));
    $params = [
        'id' => $user->getKey(),
        'hash' => sha1($user->getEmailForVerification()),
    ];
    if (config('app.shop_multilocale')) {
        $params['locale'] = app()->getLocale();
    }
    if (config('app.shop_multishop') || config('app.shop_registration')) {
        $params['site'] = $user->siteid ?: 'default';
    }
    $generatedUrl = URL::temporarySignedRoute('verification.verify', $time, $params);

    // 2. Fetch latest emails from Mailhog
    $mailhogEmails = [];
    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://mailhog:8025/api/v2/messages');
        $data = json_decode($response->getBody()->getContents(), true);
        
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                // Check if recipient matches user's email
                $to = $item['Content']['Headers']['To'][0] ?? '';
                if (str_contains($to, $user->email)) {
                    $body = $item['Content']['Body'] ?? '';
                    
                    // Try to extract verification URL from email body
                    $extractedUrl = null;
                    if (preg_match('/href="([^"]+)"/i', $body, $matches)) {
                        $extractedUrl = $matches[1];
                    } elseif (preg_match('/(https?:\/\/[^\s]+)/i', $body, $matches)) {
                        $extractedUrl = $matches[1];
                    }

                    // Try to extract OTP/digits (if any)
                    $otpCode = null;
                    if (preg_match('/\b\d{4,6}\b/', $body, $matches)) {
                        $otpCode = $matches[0];
                    }

                    $mailhogEmails[] = [
                        'subject' => $item['Content']['Headers']['Subject'][0] ?? '(No Subject)',
                        'date' => $item['Created'] ?? '',
                        'extracted_url' => $extractedUrl,
                        'otp_code' => $otpCode,
                        'raw_body' => $body
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        // Mailhog might not be running or reachable
    }

    return response()->json([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone
        ],
        'verification_url' => $generatedUrl,
        'emails_from_mailhog' => array_map(function ($emailItem) {
            return [
                'subject' => $emailItem['subject'],
                'date' => $emailItem['date'],
                'extracted_url' => $emailItem['extracted_url'],
                'otp_code' => $emailItem['otp_code']
            ];
        }, $mailhogEmails)
    ]);
});

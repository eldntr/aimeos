<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\CategoryController as ApiCategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\WalletController as SellerWalletController;
use App\Http\Controllers\Seller\VoucherController as SellerVoucherController;
use App\Http\Controllers\Seller\ReportController as SellerReportController;
use App\Models\SellerWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $response = app(SellerProductController::class)->index($request);
        $data = $response->getData(true);

        return view('pages.merchant.products.index', [
            'products' => $data['data'] ?? [],
            'total' => count($data['data'] ?? []),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.merchant.products.create', [
            'categories' => $this->loadCategories($request),
        ]);
    }

    public function edit(Request $request, $product)
    {
        $response = app(SellerProductController::class)->show($request, $product);
        $data = $response->getData(true);

        return view('pages.merchant.products.edit', [
            'product' => $data['data'] ?? [],
            'categories' => $this->loadCategories($request),
        ]);
    }

    public function store(Request $request)
    {
        $response = app(SellerProductController::class)->store($request);

        if ($request->wantsJson()) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            $data = $response->getData(true);
            $message = $data['message'] ?? 'Gagal menyimpan produk.';
            return redirect()->back()->withInput()->withErrors(['error' => $message]);
        }

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $product)
    {
        $response = app(SellerProductController::class)->update($request, $product);

        if ($request->wantsJson()) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            $data = $response->getData(true);
            $message = $data['message'] ?? 'Gagal memperbarui produk.';
            return redirect()->back()->withInput()->withErrors(['error' => $message]);
        }

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, $product)
    {
        $response = app(SellerProductController::class)->destroy($request, $product);

        if ($request->wantsJson()) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            $data = $response->getData(true);
            $message = $data['message'] ?? 'Gagal menghapus produk.';
            return redirect()->back()->withErrors(['error' => $message]);
        }

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        $merchantProfile = null;
        if ($user && $user->siteid) {
            $context = app('aimeos.context')->get(false);
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $parts = array_filter(explode('.', trim($user->siteid, '.')));
            $numericId = end($parts);
            try {
                $site = $siteManager->get($numericId);
                $merchantProfile = (object)[
                    'store_name' => $site->getLabel()
                ];
            } catch (\Exception $e) {}
        }

        $products = [];
        try {
            $response = app(SellerProductController::class)->index($request);
            $data = $response->getData(true);
            $products = $data['data'] ?? [];
        } catch (\Exception $e) {}

        $totalProducts = count($products);
        $activeProducts = collect($products)->filter(fn($p) => ($p['status'] ?? 0) > 0)->count();
        
        $latestProducts = collect($products)->slice(0, 5)->map(function($p) {
            return [
                'id' => $p['id'] ?? null,
                'name' => $p['label'] ?? $p['name'] ?? '-',
                'price' => $p['price'] ?? '-',
                'image' => $p['logo'] ?? $p['image'] ?? null
            ];
        })->all();

        $salesData = [];
        try {
            $salesResponse = app(SellerReportController::class)->getSales($request);
            $salesBody = $salesResponse->getData(true);
            $salesData = $salesBody['data'] ?? [];
        } catch (\Exception $e) {}

        // --- NEW PREMIUM MERCHANT DASHBOARD METRICS ---
        $wallet = [
            'total_revenue' => 0.0,
            'total_withdrawn' => 0.0,
            'pending_withdrawal' => 0.0,
            'available_balance' => 0.0
        ];
        $pendingEscrow = 0.0;
        $lowStockItems = [];
        $activeDisputes = [];
        $recentOrders = [];

        if ($user && $user->siteid) {
            // 1. Fetch Wallet Breakdown (Settled vs Pending Withdrawal)
            try {
                $walletResponse = app(SellerWalletController::class)->getWallet($request);
                $walletBody = $walletResponse->getData(true);
                $wallet = $walletBody['data'] ?? $wallet;
            } catch (\Exception $e) {}

            // 2. Fetch Escrow Pending Balance (statuspayment = 2)
            try {
                $rawPending = (float) \DB::table('mshop_order')
                    ->where('siteid', $user->siteid)
                    ->where('statuspayment', 2) // PAY_RECEIVED (Escrow)
                    ->sum('price');
                $globalCommissionRate = (float) \App\Models\SystemSetting::getVal('platform_commission', 5.0);
                $pendingEscrow = $rawPending - ($rawPending * $globalCommissionRate) / 100;
            } catch (\Exception $e) {}

            // 3. Fetch Low Stock Items (stocklevel <= 5)
            try {
                $lowStockItems = \DB::table('mshop_stock')
                    ->join('mshop_product', 'mshop_stock.prodid', '=', 'mshop_product.id')
                    ->where('mshop_stock.siteid', $user->siteid)
                    ->whereNotNull('mshop_stock.stocklevel')
                    ->where('mshop_stock.stocklevel', '<=', 5)
                    ->orderBy('mshop_stock.stocklevel', 'asc')
                    ->get(['mshop_product.id', 'mshop_product.label as name', 'mshop_stock.stocklevel'])
                    ->all();
            } catch (\Exception $e) {}

            // 4. Fetch Active disputes (sengketa terbuka)
            try {
                $activeDisputes = \DB::table('mshop_review')
                    ->join('mshop_order', 'mshop_review.refid', '=', 'mshop_order.id')
                    ->where('mshop_order.siteid', $user->siteid)
                    ->where('mshop_review.domain', 'order')
                    ->where('mshop_review.status', 1) // Terbuka
                    ->orderBy('mshop_review.ctime', 'desc')
                    ->get(['mshop_review.id', 'mshop_review.refid as order_id', 'mshop_review.comment as complaint', 'mshop_review.ctime'])
                    ->all();
            } catch (\Exception $e) {}

            // 5. Fetch Recent Orders
            try {
                $ordersData = \DB::table('mshop_order')
                    ->where('siteid', $user->siteid)
                    ->orderBy('ctime', 'desc')
                    ->limit(5)
                    ->get(['id', 'price', 'statuspayment', 'statusdelivery', 'ctime'])
                    ->all();
                
                foreach ($ordersData as $order) {
                    $recentOrders[] = [
                        'id' => $order->id,
                        'price' => (float)$order->price,
                        'payment_status' => $this->getPaymentStatusText($order->statuspayment),
                        'delivery_status' => $this->getDeliveryStatusText($order->statusdelivery),
                        'created_at' => $order->ctime
                    ];
                }
            } catch (\Exception $e) {}
        }

        return view('pages.merchant.dashboard', [
            'merchantProfile' => $merchantProfile,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'latestProducts' => $latestProducts,
            'salesData' => $salesData,
            'wallet' => $wallet,
            'pendingEscrow' => $pendingEscrow,
            'lowStockItems' => $lowStockItems,
            'activeDisputes' => $activeDisputes,
            'recentOrders' => $recentOrders
        ]);
    }

    private function getPaymentStatusText($code)
    {
        switch ($code) {
            case -1: return 'Dibatalkan';
            case 0: return 'Belum Bayar';
            case 1: return 'Menunggu Pembayaran';
            case 2: return 'Pembayaran Escrow';
            case 3: return 'Pembayaran Berhasil';
            case 4: return 'Refunded';
            default: return 'Pending';
        }
    }

    private function getDeliveryStatusText($code)
    {
        switch ($code) {
            case -1: return 'Gagal Kirim';
            case 0: return 'Belum Diproses';
            case 1: return 'Sedang Dipacking';
            case 2: return 'Dalam Pengiriman';
            case 3: return 'Telah Sampai';
            case 4: return 'Selesai';
            default: return 'Pending';
        }
    }

    protected function loadCategories(Request $request)
    {
        $response = app(ApiCategoryController::class)->index($request);
        $categories = Arr::get($response->getData(true), 'data', []);

        return collect($categories)
            ->map(function ($category) {
                return [
                    'id' => $category['id'] ?? null,
                    'name' => $category['label'] ?? $category['name'] ?? '-',
                ];
            })
            ->values()
            ->all();
    }

    public function ordersIndex(Request $request)
    {
        $response = app(SellerOrderController::class)->index($request);
        $data = $response->getData(true);

        return view('pages.merchant.orders.index', [
            'orders' => $data['data'] ?? [],
        ]);
    }

    public function ordersShow(Request $request, $id)
    {
        $response = app(SellerOrderController::class)->show($request, $id);
        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            return redirect()->route('merchant.orders.index')->withErrors(['error' => $data['message'] ?? 'Pesanan tidak ditemukan.']);
        }

        return view('pages.merchant.orders.show', [
            'order' => $data['data'] ?? [],
        ]);
    }

    public function ordersUpdateStatus(Request $request, $id)
    {
        $response = app(SellerOrderController::class)->updateStatus($request, $id);
        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            return redirect()->back()->withErrors(['error' => $data['message'] ?? 'Gagal memperbarui status pesanan.']);
        }

        return redirect()->route('merchant.orders.show', $id)->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function ordersComplaintResponse(Request $request, $id)
    {
        $response = app(SellerOrderController::class)->respondComplaint($request, $id);
        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            return redirect()->back()->withErrors(['error' => $data['message'] ?? 'Gagal mengirim tanggapan komplain.']);
        }

        return redirect()->route('merchant.orders.show', $id)->with('success', 'Tanggapan komplain berhasil dikirim.');
    }

    public function ordersRequestPickup(Request $request, $id)
    {
        $response = app(SellerOrderController::class)->requestPickup($request, $id);
        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            return redirect()->back()->withErrors(['error' => $data['message'] ?? 'Gagal memproses pickup.']);
        }

        return redirect()->route('merchant.orders.show', $id)->with('success', 'Permintaan pickup berhasil diajukan.');
    }

    public function walletIndex(Request $request)
    {
        $response = app(SellerWalletController::class)->getWallet($request);
        $data = $response->getData(true);
        $wallet = $data['data'] ?? [];

        $user = auth()->user();
        $withdrawals = SellerWithdrawal::where('siteid', $user->siteid)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.merchant.wallet.index', [
            'wallet' => $wallet,
            'withdrawals' => $withdrawals,
        ]);
    }

    public function walletWithdraw(Request $request)
    {
        $response = app(SellerWalletController::class)->withdraw($request);
        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            return redirect()->back()->withInput()->withErrors(['error' => $data['message'] ?? 'Gagal mengajukan penarikan dana.']);
        }

        return redirect()->route('merchant.wallet.index')->with('success', 'Permintaan penarikan dana berhasil diajukan.');
    }

    public function vouchersIndex(Request $request)
    {
        $response = app(SellerVoucherController::class)->index($request);
        $data = $response->getData(true);

        return view('pages.merchant.vouchers.index', [
            'vouchers' => $data['data'] ?? [],
        ]);
    }

    public function vouchersCreate(Request $request)
    {
        return view('pages.merchant.vouchers.create');
    }

    public function vouchersStore(Request $request)
    {
        $response = app(SellerVoucherController::class)->store($request);
        $data = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            return redirect()->back()->withInput()->withErrors(['error' => $data['message'] ?? 'Gagal membuat voucher.']);
        }

        return redirect()->route('merchant.vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function addVariantAJAX(Request $request, $id)
    {
        $response = app(SellerProductController::class)->addVariant($request, $id);
        return $response;
    }

    public function deleteVariantAJAX(Request $request, $id, $variant_id)
    {
        $response = app(SellerProductController::class)->deleteVariant($request, $id, $variant_id);
        return $response;
    }

    public function deleteImageAJAX(Request $request, $id, $image_id)
    {
        $response = app(SellerProductController::class)->deleteImage($request, $id, $image_id);
        return $response;
    }

    public function reorderImagesAJAX(Request $request, $id)
    {
        $response = app(SellerProductController::class)->reorderImages($request, $id);
        return $response;
    }
}

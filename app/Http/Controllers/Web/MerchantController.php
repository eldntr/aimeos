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

        return view('pages.merchant.dashboard', [
            'merchantProfile' => $merchantProfile,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'latestProducts' => $latestProducts,
            'salesData' => $salesData
        ]);
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
}

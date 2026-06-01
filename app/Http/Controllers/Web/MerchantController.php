<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\CategoryController as ApiCategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
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

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $product)
    {
        $response = app(SellerProductController::class)->update($request, $product);

        if ($request->wantsJson()) {
            return $response;
        }

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, $product)
    {
        $response = app(SellerProductController::class)->destroy($request, $product);

        if ($request->wantsJson()) {
            return $response;
        }

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil dihapus.');
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
}

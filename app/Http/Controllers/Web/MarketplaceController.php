<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CategoryController as ApiCategoryController;
use App\Http\Controllers\ProductController as ApiProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class MarketplaceController extends Controller
{
    public function landing(Request $request)
    {
        $categories = $this->loadCategories($request);
        $trendingProducts = $this->loadTrendingProducts($request);
        $priceDroppedProducts = $this->loadPriceDroppedProducts($request);
        $banners = $this->loadBanners($request);

        return view('pages.marketplace.landing', [
            'categories' => array_slice($categories, 0, 6),
            'trendingProducts' => array_slice($trendingProducts, 0, 8),
            'priceDroppedProducts' => array_slice($priceDroppedProducts, 0, 8),
            'banners' => $banners,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('search', '');
        $products = $this->loadProducts($request);
        $categories = $this->loadCategories($request);

        return view('pages.marketplace.search', [
            'products' => $products,
            'query' => $query,
            'categories' => $categories
        ]);
    }

    public function categories(Request $request)
    {
        return view('pages.marketplace.categories', [
            'categories' => $this->loadCategories($request),
            'title' => 'Eksplor Kategori',
            'subtitle' => 'Temukan berbagai barang preloved berkualitas berdasarkan kategori yang kamu butuhkan.',
        ]);
    }

    public function showCategory(Request $request, string $selected_category)
    {
        $categories = $this->loadCategories($request);
        $category = $this->findCategory($categories, $selected_category);

        if (! $category) {
            abort(404);
        }

        $products = $this->loadProductsByCategory($request, $category['id']);

        // Apply Price Filtering
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        if (is_numeric($minPrice)) {
            $products = array_filter($products, function ($p) use ($minPrice) {
                return ($p['priceRaw'] ?? 0) >= $minPrice;
            });
        }
        if (is_numeric($maxPrice)) {
            $products = array_filter($products, function ($p) use ($maxPrice) {
                return ($p['priceRaw'] ?? 0) <= $maxPrice;
            });
        }

        // Apply Sorting
        $sort = $request->query('sort');
        if ($sort === 'price_asc') {
            usort($products, function ($a, $b) {
                return ($a['priceRaw'] ?? 0) <=> ($b['priceRaw'] ?? 0);
            });
        } elseif ($sort === 'price_desc') {
            usort($products, function ($a, $b) {
                return ($b['priceRaw'] ?? 0) <=> ($a['priceRaw'] ?? 0);
            });
        } elseif ($sort === 'latest') {
            usort($products, function ($a, $b) {
                return strcmp($b['id'] ?? '', $a['id'] ?? '');
            });
        }

        return view('pages.marketplace.category-detail', [
            'category' => $category,
            'title' => $category['name'],
            'subtitle' => 'Menampilkan produk berkualitas di kategori ' . $category['name'],
            'products' => array_values($products), // reset keys after array_filter
        ]);
    }

    public function productDetail(Request $request, string $id)
    {
        $product = $this->loadProduct($request, $id);
        if (! $product) {
            abort(404);
        }

        return view('pages.marketplace.product-detail', [
            'product' => $product,
            'reviews' => $this->loadReviews($id),
            'relatedProducts' => array_slice($this->loadProducts($request), 0, 3),
        ]);
    }

    public function shopDetail(Request $request, string $shop_code)
    {
        $response = app(\App\Http\Controllers\ShopController::class)->show($request, $shop_code);
        $body = $response->getData(true);
        $shop = Arr::get($body, 'data');

        if (!$shop) {
            abort(404);
        }

        $productsResponse = app(\App\Http\Controllers\ShopController::class)->getProducts($request, $shop_code);
        $productsBody = $productsResponse->getData(true);
        $products = Arr::get($productsBody, 'data', []);

        $formattedProducts = array_map(function (array $product) {
            return $this->formatProduct($product);
        }, $products);

        return view('pages.marketplace.shop-detail', [
            'shop' => $shop,
            'products' => $formattedProducts,
            'total' => count($formattedProducts),
        ]);
    }

    protected function loadCategories(Request $request): array
    {
        $response = app(ApiCategoryController::class)->index($request);
        $body = $response->getData(true);
        $categories = Arr::get($body, 'data', []);

        return array_map(function (array $category) {
            return $this->formatCategory($category);
        }, $categories);
    }

    protected function loadBanners(Request $request): array
    {
        try {
            $response = app(\App\Http\Controllers\HomeController::class)->getBanners($request);
            $body = $response->getData(true);
            $banners = Arr::get($body, 'data', []);

            return array_map(function (array $banner) {
                $images = Arr::get($banner, 'images', []);
                return [
                    'id' => $banner['id'] ?? null,
                    'code' => $banner['code'] ?? '',
                    'label' => $banner['label'] ?? '',
                    'image' => Arr::get($images, '0.url'),
                ];
            }, $banners);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function loadProducts(Request $request): array
    {
        $response = app(ApiProductController::class)->index($request);
        $body = $response->getData(true);
        $products = Arr::get($body, 'data', []);

        return array_map(function (array $product) {
            return $this->formatProduct($product);
        }, $products);
    }

    protected function loadProductsByCategory(Request $request, string $categoryId): array
    {
        $response = app(ApiCategoryController::class)->getProducts($request, $categoryId);
        $body = $response->getData(true);
        $products = Arr::get($body, 'data', []);

        return array_map(function (array $product) {
            return $this->formatProduct($product);
        }, $products);
    }

    protected function loadProduct(Request $request, string $id): ?array
    {
        $response = app(ApiProductController::class)->show($request, $id);
        $body = $response->getData(true);
        $product = Arr::get($body, 'data');

        return is_array($product) ? $this->formatProduct($product, true) : null;
    }

    protected function loadReviews(string $productId): array
    {
        try {
            $response = app(\App\Http\Controllers\ReviewController::class)->index($productId);
            $body = $response->getData(true);
            $items = Arr::get($body, 'data', []);
            
            return array_map(function ($item) {
                $comment = $item['review.comment'] ?? '';
                $photo = null;
                if (preg_match('/\[Attached Photo:\s*([^\]]+)\]/', $comment, $matches)) {
                    $photo = trim($matches[1]);
                    $comment = trim(str_replace($matches[0], '', $comment));
                }
                
                return [
                    'id' => $item['review.id'] ?? null,
                    'name' => $item['review.name'] ?? 'Pembeli',
                    'rating' => $item['review.rating'] ?? 5,
                    'comment' => $comment,
                    'photo' => $photo,
                    'time' => isset($item['review.ctime']) ? date('d M Y', strtotime($item['review.ctime'])) : null,
                ];
            }, $items);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function formatCategory(array $category): array
    {
        return [
            'id' => $category['id'] ?? null,
            'name' => $category['label'] ?? ($category['name'] ?? 'Kategori'),
            'slug' => $category['code'] ?? null,
            'icon' => $this->mapCategoryIcon($category['label'] ?? ($category['code'] ?? '')), 
            'children' => array_map(function (array $child) {
                return $this->formatCategory($child);
            }, $category['children'] ?? []),
        ];
    }

    protected function formatProduct(array $product, bool $detail = false): array
    {
        $images = Arr::get($product, 'images', []);
        $price = Arr::get($product, 'price');
        
        $prices = Arr::get($product, 'prices', []);
        $priceRaw = 0;
        if (!empty($prices)) {
            $priceRaw = Arr::get($prices, '0.value', 0);
        } else {
            // Check if prices contains raw value directly in some other format
            $priceRaw = Arr::get($product, 'priceRaw', 0);
        }

        $priceLabel = 'Rp 0';
        if ($price) {
            $cleanPrice = preg_replace('/[a-zA-Z\s]+/', '', $price);
            $priceLabel = 'Rp ' . trim($cleanPrice);
        } elseif ($priceRaw > 0) {
            $priceLabel = 'Rp ' . number_format($priceRaw, 0, ',', '.');
        }

        $shopCode = Arr::get($product, 'shop_code', 'default');
        $sellerUserId = null;
        if ($shopCode !== 'default') {
            $site = \DB::table('mshop_locale_site')->where('code', $shopCode)->first();
            if ($site) {
                $seller = \DB::table('users')->where('siteid', $site->id)->first();
                if ($seller) {
                    $sellerUserId = $seller->id;
                }
            }
        }

        return [
            'id' => $product['id'] ?? null,
            'name' => $product['label'] ?? ($product['code'] ?? 'Produk'),
            'brand' => strtoupper($product['type'] ?? 'Prelove'),
            'price' => $priceLabel,
            'priceRaw' => $priceRaw,
            'image' => Arr::get($images, '0.url', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1200&q=80'),
            'rating' => Arr::get($product, 'rating', '-'),
            'location' => Arr::get($product, 'location', 'Indonesia'),
            'shop_name' => Arr::get($product, 'shop_name', 'Toko Reborns'),
            'shop_code' => $shopCode,
            'seller_user_id' => $sellerUserId,
            'description' => Arr::get($product, 'description') ?? ($detail ? ($product['label'] ?? 'Deskripsi produk belum tersedia.') : null),
            'badge' => Arr::get($product, 'badge'),
            'badgeType' => Arr::get($product, 'badgeType', 'success'),
            'link' => Route::has('products.show') && isset($product['id']) ? route('products.show', ['id' => $product['id']]) : null,
        ];
    }

    protected function mapCategoryIcon(string $label): string
    {
        $label = strtolower($label);

        if (str_contains($label, 'elektronik')) {
            return 'headphones';
        }

        if (str_contains($label, 'pakaian') || str_contains($label, 'tas') || str_contains($label, 'sepatu')) {
            return 'checkroom';
        }

        if (str_contains($label, 'aksesoris')) {
            return 'diamond';
        }

        if (str_contains($label, 'hobi') || str_contains($label, 'olahraga')) {
            return 'sports_soccer';
        }

        if (str_contains($label, 'rumah') || str_contains($label, 'dekor')) {
            return 'weekend';
        }

        return 'category';
    }

    protected function findCategory(array $categories, string $selected_category): ?array
    {
        foreach ($categories as $category) {
            if ((string) $category['id'] === $selected_category || $category['slug'] === $selected_category) {
                return $category;
            }

            if (!empty($category['children'])) {
                $found = $this->findCategory($category['children'], $selected_category);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    protected function loadTrendingProducts(Request $request): array
    {
        try {
            $productIds = \DB::table('mshop_product')
                ->where('status', 1)
                ->orderByDesc('rating')
                ->orderByDesc('ratings')
                ->limit(20)
                ->pluck('id')
                ->toArray();

            if (empty($productIds)) {
                return [];
            }

            $req = new Request(['ids' => $productIds]);
            $response = app(ApiProductController::class)->index($req);
            $body = $response->getData(true);
            $products = Arr::get($body, 'data', []);

            $idPositions = array_flip($productIds);
            usort($products, function ($a, $b) use ($idPositions) {
                return ($idPositions[$a['id']] ?? 999) <=> ($idPositions[$b['id']] ?? 999);
            });

            return array_map(function (array $product) {
                return $this->formatProduct($product);
            }, $products);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function loadPriceDroppedProducts(Request $request): array
    {
        try {
            $productIds = \DB::table('mshop_product_list')
                ->join('mshop_price', 'mshop_product_list.refid', '=', 'mshop_price.id')
                ->where('mshop_product_list.domain', 'price')
                ->where('mshop_price.rebate', '>', 0)
                ->where('mshop_price.status', 1)
                ->orderByDesc('mshop_price.rebate')
                ->limit(20)
                ->pluck('mshop_product_list.parentid')
                ->unique()
                ->toArray();

            if (empty($productIds)) {
                return [];
            }

            $req = new Request(['ids' => $productIds]);
            $response = app(ApiProductController::class)->index($req);
            $body = $response->getData(true);
            $products = Arr::get($body, 'data', []);

            $idPositions = array_flip($productIds);
            usort($products, function ($a, $b) use ($idPositions) {
                return ($idPositions[$a['id']] ?? 999) <=> ($idPositions[$b['id']] ?? 999);
            });

            return array_map(function (array $product) {
                return $this->formatProduct($product);
            }, $products);
        } catch (\Exception $e) {
            return [];
        }
    }
}

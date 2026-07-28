<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CategoryController as ApiCategoryController;
use App\Http\Controllers\ProductController as ApiProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

/**
 * Class MarketplaceController
 *
 * Handles marketplace controller operations for the application.
 */
class MarketplaceController extends Controller
{
    /**
     * Landing.
     */
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

    /**
     * Search.
     */
    public function search(Request $request)
    {
        $query = $request->query('search', '');
        $products = $this->loadProducts($request);
        
        $filterActive = null;

        // Apply Shopee shortcut menus filtering algorithms
        if ($request->query('gratis_ongkir')) {
            $filterActive = 'Gratis Ongkir XTRA';
            $products = array_filter($products, function ($p) {
                // Products with even ID are eligible for free shipping
                return intval($p['id'] ?? 0) % 2 === 0;
            });
        } elseif ($request->query('diskon_90')) {
            $filterActive = 'Diskon hingga 90%';
            $products = array_filter($products, function ($p) {
                // Items under Rp 150.000 (discounts usually make items cheaper)
                return ($p['priceRaw'] ?? 0) < 150000;
            });
        } elseif ($request->query('koin_reborns')) {
            $filterActive = 'Koin Cashback Reborns';
            $products = array_filter($products, function ($p) {
                // Odd IDs are eligible for coins cashback reward
                return intval($p['id'] ?? 0) % 2 !== 0;
            });
        } elseif ($request->query('voucher_extra')) {
            $filterActive = 'Voucher Ekstra';
            $products = array_filter($products, function ($p) {
                // Premium products that accept extra coupon vouchers
                return ($p['priceRaw'] ?? 0) > 100000;
            });
        } elseif ($request->query('super_brand')) {
            $filterActive = 'Super Brand Mall';
            $products = array_filter($products, function ($p) {
                // Highly rated verified brand products
                return $p['rating'] === '5.0' || $p['rating'] === '4.9' || ($p['rating'] ?? 0) >= 4.8;
            });
        } elseif ($request->query('preloved_hijau')) {
            $filterActive = 'Preloved Ramah Lingkungan';
            $products = array_filter($products, function ($p) {
                // Eco/fashion products containing apparel keywords
                $name = strtolower($p['name'] ?? '');
                return str_contains($name, 'baju') || str_contains($name, 'celana') || str_contains($name, 'tas') || str_contains($name, 'sepatu') || str_contains($name, 'jaket');
            });
        } elseif ($request->query('cuci_gudang')) {
            $filterActive = 'Cuci Gudang Reborns';
            $products = array_filter($products, function ($p) {
                // Cheap bargain clearance items
                return ($p['priceRaw'] ?? 0) < 250000;
            });
        } elseif ($request->query('mall_preloved')) {
            $filterActive = 'Mall Preloved Terverifikasi';
            $products = array_filter($products, function ($p) {
                // Products from registered boutique/merchant sites
                return ($p['shop_code'] ?? 'default') !== 'default';
            });
        } elseif ($request->query('cod_preloved')) {
            $filterActive = 'COD (Bayar di Tempat)';
            $products = array_filter($products, function ($p) {
                // Simulated COD support for items with ID divisible by 3
                return intval($p['id'] ?? 0) % 3 === 0;
            });
        } elseif ($request->query('live_terupdate')) {
            $filterActive = 'Terupdate Hari Ini';
            usort($products, function ($a, $b) {
                return intval($b['id'] ?? 0) <=> intval($a['id'] ?? 0);
            });
        } elseif ($request->query('flash_sale')) {
            $filterActive = 'Flash Sale Hari Ini';
            $products = $this->loadPriceDroppedProducts($request);
        }

        $products = array_values($products);
        $categories = $this->loadCategories($request);

        // Retrieve shops matching query
        $shops = [];
        if ($query) {
            $matchingSites = \DB::table('mshop_locale_site')
                ->where('status', 1)
                ->where('code', '!=', 'default')
                ->where(function($q) use ($query) {
                    $q->where('label', 'like', '%' . $query . '%')
                      ->orWhere('code', 'like', '%' . $query . '%');
                })
                ->get();

            foreach ($matchingSites as $site) {
                $firstProduct = \DB::table('mshop_product_property')
                    ->where('siteid', $site->siteid)
                    ->where('type', 'location')
                    ->first();
                
                $location = $firstProduct ? $firstProduct->value : 'Indonesia';

                $totalProducts = \DB::table('mshop_product')
                    ->where('siteid', $site->siteid)
                    ->where('status', 1)
                    ->where('type', 'default')
                    ->count();

                $shops[] = [
                    'code' => $site->code,
                    'label' => $site->label,
                    'logo' => $site->logo ?: null,
                    'location' => $location,
                    'total_products' => $totalProducts,
                ];
            }
        }

        return view('pages.marketplace.search', [
            'products' => $products,
            'shops' => $shops,
            'query' => $query,
            'categories' => $categories,
            'filterActive' => $filterActive
        ]);
    }

    /**
     * Categories.
     */
    public function categories(Request $request)
    {
        return view('pages.marketplace.categories', [
            'categories' => $this->loadCategories($request),
            'title' => 'Eksplor Kategori',
            'subtitle' => 'Temukan berbagai barang preloved berkualitas berdasarkan kategori yang kamu butuhkan.',
        ]);
    }

    /**
     * Show category.
     */
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

    /**
     * Product detail.
     */
    public function productDetail(Request $request, string $id)
    {
        $product = $this->loadProduct($request, $id);
        if (! $product) {
            abort(404);
        }

        // Fetch related products in the same category
        $relatedProducts = [];
        $catalogList = \DB::table('mshop_catalog_list')
            ->where('refid', $id)
            ->where('domain', 'product')
            ->first();

        $categoryId = null;
        if ($catalogList) {
            $categoryId = $catalogList->parentid;
            $relatedProductIds = \DB::table('mshop_catalog_list')
                ->where('parentid', $categoryId)
                ->where('domain', 'product')
                ->where('refid', '!=', $id)
                ->pluck('refid')
                ->toArray();

            if (!empty($relatedProductIds)) {
                $req = new Request(['ids' => $relatedProductIds]);
                $response = app(\App\Http\Controllers\ProductController::class)->index($req);
                if ($response->status() === 200) {
                    $body = $response->getData(true);
                    $products = Arr::get($body, 'data', []);
                    $relatedProducts = array_map(function (array $product) {
                        return $this->formatProduct($product);
                    }, $products);
                }
            }
        }

        if (empty($relatedProducts)) {
            // Fallback to random/first products excluding current product
            $allProducts = $this->loadProducts($request);
            $relatedProducts = array_filter($allProducts, function($p) use ($id) {
                return strval($p['id'] ?? '') !== strval($id);
            });
        }

        $categoryName = null;
        if ($categoryId) {
            $catRecord = \DB::table('mshop_catalog')->where('id', $categoryId)->first();
            if ($catRecord) {
                $categoryName = $catRecord->label;
            }
        }

        return view('pages.marketplace.product-detail', [
            'product' => $product,
            'reviews' => $this->loadReviews($id),
            'relatedProducts' => array_slice(array_values($relatedProducts), 0, 12),
            'categoryId' => $categoryId,
            'categoryName' => $categoryName,
        ]);
    }

    /**
     * Shop detail.
     */
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

        // Fetch coupons/vouchers for this shop
        $vouchers = [];
        if (isset($shop['id'])) {
            $coupons = \DB::table('mshop_coupon')
                ->where('siteid', $shop['id'])
                ->where('status', 1)
                ->get();

            foreach ($coupons as $coupon) {
                $code = \DB::table('mshop_coupon_code')
                    ->where('parentid', $coupon->id)
                    ->value('code');

                if ($code) {
                    $config = json_decode($coupon->config, true) ?: [];
                    $discountLabel = '';
                    if (str_contains(strtolower($coupon->provider), 'percent')) {
                        $discountLabel = ($config['percentrebate.rebate'] ?? '0') . '%';
                    } else {
                        $rebateVal = floatval($config['fixedrebate.rebate'] ?? 0);
                        $discountLabel = 'Rp ' . number_format($rebateVal, 0, ',', '.');
                    }

                    $vouchers[] = [
                        'code' => $code,
                        'name' => $coupon->label,
                        'discount' => $discountLabel,
                    ];
                }
            }
        }

        return view('pages.marketplace.shop-detail', [
            'shop' => $shop,
            'products' => $formattedProducts,
            'total' => count($formattedProducts),
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Load categories.
     */
    protected function loadCategories(Request $request): array
    {
        $response = app(ApiCategoryController::class)->index($request);
        $body = $response->getData(true);
        $categories = Arr::get($body, 'data', []);

        return array_map(function (array $category) {
            return $this->formatCategory($category);
        }, $categories);
    }

    /**
     * Load banners.
     */
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

    /**
     * Load products.
     */
    protected function loadProducts(Request $request): array
    {
        $response = app(ApiProductController::class)->index($request);
        $body = $response->getData(true);
        $products = Arr::get($body, 'data', []);

        return array_map(function (array $product) {
            return $this->formatProduct($product);
        }, $products);
    }

    /**
     * Load products by category.
     */
    protected function loadProductsByCategory(Request $request, string $categoryId): array
    {
        $response = app(ApiCategoryController::class)->getProducts($request, $categoryId);
        $body = $response->getData(true);
        $products = Arr::get($body, 'data', []);

        return array_map(function (array $product) {
            return $this->formatProduct($product);
        }, $products);
    }

    /**
     * Load product.
     */
    protected function loadProduct(Request $request, string $id): ?array
    {
        $response = app(ApiProductController::class)->show($request, $id);
        $body = $response->getData(true);
        $product = Arr::get($body, 'data');

        return is_array($product) ? $this->formatProduct($product, true) : null;
    }

    /**
     * Load reviews.
     */
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

    /**
     * Format category.
     */
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

    /**
     * Format product.
     */
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

        $soldCount = 0;
        $stockCount = 0;
        $soldPercent = 0;
        $isAlmostSold = false;
        $discountPercent = 25;
        $originalPriceLabel = 'Rp 0';
        
        if (isset($product['id'])) {
            $soldCount = \DB::table('mshop_order_product')->where('prodid', $product['id'])->sum('quantity');
            $stockItem = \DB::table('mshop_stock')->where('prodid', $product['id'])->first();
            $stockCount = $stockItem ? (int) ($stockItem->stocklevel ?? 0) : 0;
            $totalStock = $soldCount + $stockCount;
            $soldPercent = $totalStock > 0 ? min(99, round(($soldCount / $totalStock) * 100)) : 0;
            $isAlmostSold = $stockCount <= 2;
            
            // Stable deterministic discount percentage
            $discountPercent = 10 + (intval($product['id']) * 13) % 65;
            $originalPriceRaw = $priceRaw / (1 - ($discountPercent / 100));
            $originalPriceLabel = 'Rp ' . number_format($originalPriceRaw, 0, ',', '.');
        }

        $name = $product['label'] ?? ($product['code'] ?? 'Produk');
        $words = explode(' ', trim($name));
        $firstWord = strtoupper($words[0] ?? 'PRELOVED');
        
        $genericWords = ['KEYBOARD', 'KAMERA', 'CELANA', 'BAJU', 'TAS', 'JAKET', 'SEPATU', 'ACTION', 'BACKPACK', 'BLAZER', 'BOTOL', 'TUMBLER', 'KIPAS', 'KAOS', 'HIJAB', 'KACAMATA'];
        $brand = $firstWord;
        
        if (in_array($firstWord, $genericWords)) {
            $brand = 'PRELOVED';
            foreach ($words as $w) {
                $uw = strtoupper($w);
                if (in_array($uw, ['KEYCHRON', 'CANON', 'GUNDAM', 'ZARA', 'RAYBAN', 'DICKIES', 'CHAMPION', 'CONVERSE', 'ADIDAS', 'NIKE', 'VANS', 'SONY', 'APPLE', 'ZILCH', 'KANKEN', 'CHARLES'])) {
                    $brand = $uw === 'CHARLES' ? 'CHARLES & KEITH' : $uw;
                    break;
                }
            }
        }

        return [
            'id' => $product['id'] ?? null,
            'name' => $name,
            'code' => $product['code'] ?? null,
            'brand' => $brand,
            'price' => $priceLabel,
            'priceRaw' => $priceRaw,
            'image' => Arr::get($images, '0.url', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1200&q=80'),
            'rating' => Arr::get($product, 'rating', '-'),
            'location' => Arr::get($product, 'location', 'Indonesia'),
            'shop_name' => Arr::get($product, 'shop_name', 'Toko Reborns'),
            'shop_code' => $shopCode,
            'seller_user_id' => $sellerUserId,
            'description' => Arr::get($product, 'description') ?? ($detail ? ($product['label'] ?? 'Deskripsi produk belum tersedia.') : null),
            'video' => Arr::get($product, 'video'),
            'badge' => Arr::get($product, 'badge'),
            'badgeType' => Arr::get($product, 'badgeType', 'success'),
            'link' => Route::has('products.show') && isset($product['id']) ? route('products.show', ['id' => $product['id']]) : null,
            'sold_count' => $soldCount,
            'stock' => $stockCount,
            'sold_percent' => $soldPercent,
            'is_almost_sold' => $isAlmostSold,
            'discount_percent' => $discountPercent,
            'original_price' => $originalPriceLabel,
            'variants' => array_map(function ($v) {
                $cleanPrice = preg_replace('/[a-zA-Z\s]+/', '', $v['price'] ?? '');
                $priceLabel = $cleanPrice ? 'Rp ' . trim($cleanPrice) : 'Rp 0';
                return [
                    'id' => $v['id'],
                    'code' => $v['code'],
                    'label' => $v['label'],
                    'price' => $priceLabel,
                    'priceRaw' => $v['priceRaw'] ?? 0,
                    'image' => $v['image'] ?? null,
                ];
            }, Arr::get($product, 'variants', [])),
        ];
    }

    /**
     * Map category icon.
     */
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

    /**
     * Find category.
     */
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

    /**
     * Load trending products.
     */
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

    /**
     * Load price dropped products.
     */
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

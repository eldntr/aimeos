<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class ProductController
 *
 * Handles product controller operations for the application.
 */
class ProductController extends Controller
{
    /**
     * List all active products. Optionally filter by seller site code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $context = app('aimeos.context')->get(false);

        $siteCodeQuery = $request->query('site') ?: ($request->route('site') ?: 'default');
        if ($siteCodeQuery === '1.') {
            $siteCodeQuery = 'reborns';
        }
        $search   = $request->query('search', '');
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = 20;

        try {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            try {
                $site = $siteManager->find($siteCodeQuery);
            } catch (\Exception $ex) {
                if ($request->has('site')) {
                    throw $ex;
                }
                $siteCodeQuery = 'default';
                $site = $siteManager->find($siteCodeQuery);
            }

            if ($siteCodeQuery === 'default') {
                $siteFilter = $siteManager->filter(true);
                $siteItems = $siteManager->search($siteFilter);
                $siteIds = [];
                foreach ($siteItems as $item) {
                    $siteIds[] = $item->getSiteId();
                }
                
                $sites = [
                    0 => $site->getSiteId(),
                    1 => $siteIds,
                    2 => $site->getSiteId(),
                    3 => $siteIds
                ];
                $locale = new \Aimeos\MShop\Locale\Item\Standard(['locale.siteid' => $site->getSiteId()], $site, $sites);
            } else {
                $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCodeQuery, '', '', false);
            }
            $context->setLocale($locale);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Site not found.'], 404);
        }

        $context->config()->set('mshop/locale/site/level', 4);
        $manager = \Aimeos\MShop::create($context, 'product');
        $filter  = $manager->filter(true); // status=1 (active only)

        $ids = $request->query('ids', []);
        if (!empty($ids)) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $filter->add($filter->compare('==', 'product.id', $ids));
        }

        if ($search) {
            $conditions = [];
            $conditions[] = $filter->compare('~=', 'product.label', $search);
            
            try {
                $siteFilter = $siteManager->filter();
                $siteFilter->add($siteFilter->compare('~=', 'locale.site.label', $search));
                $foundSites = $siteManager->search($siteFilter);
                
                if (!$foundSites->isEmpty()) {
                    $matchingSiteIds = [];
                    foreach ($foundSites as $siteItem) {
                        $matchingSiteIds[] = $siteItem->getSiteId();
                    }
                    $conditions[] = $filter->compare('==', 'product.siteid', $matchingSiteIds);
                }
            } catch (\Exception $e) {
                // ignore
            }
            
            $filter->add($filter->or($conditions));
        }

        $filter->slice(($page - 1) * $perPage, $perPage);
        $products = $manager->search($filter);

        $data = [];
        foreach ($products as $product) {
            $siteDetails = $this->getSiteDetailsFromSiteId($context, $product->getSiteId());
            $siteCode = $siteDetails['code'] ?? 'default';

            // Boot to product native context temporarily to load correct MinIO media domain URLs & prices
            try {
                $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
                $context->setLocale($locale);
                
                $siteManager = \Aimeos\MShop::create($context, 'product');
                $siteProduct = $siteManager->get($product->getId(), ['media', 'price']);
            } catch (\Exception $e) {
                $siteProduct = $product;
            }

            $images = [];
            foreach ($siteProduct->getListItems('media', 'default') as $listItem) {
                if ($mediaItem = $listItem->getRefItem()) {
                    $images[] = [
                        'url' => $mediaItem->getUrl(),
                        'preview' => $mediaItem->getPreview(),
                    ];
                }
            }

            $prices = [];
            foreach ($siteProduct->getListItems('price', 'default') as $listItem) {
                if ($priceItem = $listItem->getRefItem()) {
                    $prices[] = [
                        'value' => $priceItem->getValue(),
                        'currency' => $priceItem->getCurrencyId(),
                    ];
                }
            }

            $priceLabel = null;
            if (!empty($prices)) {
                $firstPrice = $prices[0];
                $priceLabel = number_format($firstPrice['value'], 0, ',', '.') . ' ' . strtoupper($firstPrice['currency']);
            }

            $data[] = [
                'id'       => $product->getId(),
                'code'     => $product->getCode(),
                'label'    => $product->getLabel(),
                'type'     => $product->getType(),
                'status'   => $product->getStatus(),
                'ctime'    => $product->getTimeCreated(),
                'mtime'    => $product->getTimeModified(),
                'images'   => $images,
                'image'    => $images[0]['url'] ?? null,
                'prices'   => $prices,
                'price'    => $priceLabel,
                'rating'   => $product->getRating(),
                'ratings'  => $product->getRatings(),
                'shop_name' => $siteDetails['name'],
                'shop_code' => $siteDetails['code'],
                'video'     => \Illuminate\Support\Facades\DB::table('mshop_product')->where('id', $product->getId())->value('video'),
            ];
        }

        // Restore context to default site
        try {
            $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCodeQuery ?? 'default', '', '', false);
            $context->setLocale($locale);
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json([
            'data'  => $data,
            'meta'  => [
                'site'    => $siteCodeQuery,
                'page'    => $page,
                'perPage' => $perPage,
                'count'   => count($data),
            ],
        ]);
    }

    /**
     * Show detail of a single product by ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context  = app('aimeos.context')->get(false);
        
        // 1. Initial query in 'default' context to resolve the product's actual site code
        try {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $site = $siteManager->find('default');
            $siteFilter = $siteManager->filter(true);
            $siteItems = $siteManager->search($siteFilter);
            $siteIds = [];
            foreach ($siteItems as $item) {
                $siteIds[] = $item->getSiteId();
            }
            
            $sites = [
                0 => $site->getSiteId(),
                1 => $siteIds,
                2 => $site->getSiteId(),
                3 => $siteIds
            ];
            $locale = new \Aimeos\MShop\Locale\Item\Standard(['locale.siteid' => $site->getSiteId()], $site, $sites);
            $context->setLocale($locale);

            $manager = \Aimeos\MShop::create($context, 'product');
            $product = $manager->get($id);
            $siteDetails = $this->getSiteDetailsFromSiteId($context, $product->getSiteId());
            $siteCode = $siteDetails['code'] ?? 'default';
        } catch (\Exception $e) {
            $siteCode = 'default';
        }

        // 2. Bootstrap context to the product's actual native site code
        try {
            $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
            $context->setLocale($locale);
        } catch (\Exception $e) {
            // fallback to default
            try {
                $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
                $context->setLocale($locale);
            } catch (\Exception $ex) {
                // ignore
            }
        }

        // 3. Fetch product again inside its native site context to resolve correct media, prices, etc.
        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            $product = $manager->get($id, ['media', 'price', 'catalog', 'text', 'product']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $images = [];
        foreach ($product->getListItems('media', 'default') as $listItem) {
            if ($mediaItem = $listItem->getRefItem()) {
                $images[] = [
                    'url' => $mediaItem->getUrl(),
                    'preview' => $mediaItem->getPreview(),
                ];
            }
        }

        $prices = [];
        foreach ($product->getListItems('price', 'default') as $listItem) {
            if ($priceItem = $listItem->getRefItem()) {
                $prices[] = [
                    'value' => $priceItem->getValue(),
                    'currency' => $priceItem->getCurrencyId(),
                ];
            }
        }

        $priceLabel = null;
        if (!empty($prices)) {
            $firstPrice = $prices[0];
            $priceLabel = number_format($firstPrice['value'], 0, ',', '.') . ' ' . strtoupper($firstPrice['currency']);
        }

        $description = '';
        foreach ($product->getListItems('text', 'default') as $listItem) {
            if ($textItem = $listItem->getRefItem()) {
                if ($textItem->getType() === 'short') {
                    $description = $textItem->getContent();
                    break;
                }
            }
        }

        // Fetch variants if this is a select product
        $variants = [];
        foreach ($product->getListItems('product', 'default') as $listItem) {
            try {
                $variantProd = $manager->get($listItem->getRefId(), ['media', 'price']);
                
                $varImages = [];
                foreach ($variantProd->getListItems('media', 'default') as $varMediaListItem) {
                    if ($varMediaItem = $varMediaListItem->getRefItem()) {
                        $varImages[] = [
                            'url' => $varMediaItem->getUrl(),
                            'preview' => $varMediaItem->getPreview(),
                        ];
                    }
                }
                
                $varPrices = [];
                foreach ($variantProd->getListItems('price', 'default') as $varPriceListItem) {
                    if ($varPriceItem = $varPriceListItem->getRefItem()) {
                        $varPrices[] = [
                            'value' => $varPriceItem->getValue(),
                            'currency' => $varPriceItem->getCurrencyId(),
                        ];
                    }
                }
                
                $varPriceLabel = null;
                if (!empty($varPrices)) {
                    $firstVarPrice = $varPrices[0];
                    $varPriceLabel = number_format($firstVarPrice['value'], 0, ',', '.') . ' ' . strtoupper($firstVarPrice['currency']);
                }

                $variants[] = [
                    'id' => $variantProd->getId(),
                    'code' => $variantProd->getCode(),
                    'label' => $variantProd->getLabel(),
                    'price' => $varPriceLabel,
                    'priceRaw' => !empty($varPrices) ? $varPrices[0]['value'] : 0,
                    'images' => $varImages,
                    'image' => $varImages[0]['url'] ?? null,
                ];
            } catch (\Exception $e) {
                // skip
            }
        }

        return response()->json([
            'data' => [
                'id'          => $product->getId(),
                'code'        => $product->getCode(),
                'label'       => $product->getLabel(),
                'type'        => $product->getType(),
                'status'      => $product->getStatus(),
                'ctime'       => $product->getTimeCreated(),
                'mtime'       => $product->getTimeModified(),
                'images'      => $images,
                'image'       => $images[0]['url'] ?? null,
                'prices'      => $prices,
                'price'       => $priceLabel,
                'rating'      => $product->getRating(),
                'ratings'     => $product->getRatings(),
                'description' => $description,
                'shop_name'   => ($siteDetails = $this->getSiteDetailsFromSiteId($context, $product->getSiteId()))['name'],
                'shop_code'   => $siteDetails['code'],
                'variants'    => $variants,
                'video'       => \Illuminate\Support\Facades\DB::table('mshop_product')->where('id', $product->getId())->value('video'),
            ],
        ]);
    }

    /**
     * Get variant options for a specific product (usually of type 'select').
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVariants(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context  = app('aimeos.context')->get(false);
        $siteCode = $request->query('site') ?: ($request->route('site') ?: 'default');
        if ($siteCode === '1.') {
            $siteCode = 'reborns';
        }

        try {
            try {
                $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
            } catch (\Exception $ex) {
                $siteCode = 'default';
                $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
            }
            $context->setLocale($locale);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Site not found.'], 404);
        }

        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            // Fetch product and its product list items (which links variants)
            $product = $manager->get($id, ['product']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // if product is not a select type, it likely has no variants
        if ($product->getType() !== 'select') {
            return response()->json(['data' => []]);
        }

        $variants = [];
        $variantIds = [];

        // Collect all variant IDs
        foreach ($product->getListItems('product', 'default') as $listItem) {
            $variantIds[] = $listItem->getRefId();
        }

        if (empty($variantIds)) {
            return response()->json(['data' => []]);
        }

        // Fetch variant products
        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'product.id', $variantIds));
        $filter->add($filter->compare('==', 'product.status', 1));
        
        $variantProducts = $manager->search($filter, ['price', 'media']);

        foreach ($variantProducts as $variant) {
            $images = [];
            foreach ($variant->getListItems('media', 'default') as $mediaList) {
                if ($mediaItem = $mediaList->getRefItem()) {
                    $images[] = [
                        'url' => $mediaItem->getUrl(),
                        'preview' => $mediaItem->getPreview(),
                    ];
                }
            }

            $prices = [];
            foreach ($variant->getListItems('price', 'default') as $priceList) {
                if ($priceItem = $priceList->getRefItem()) {
                    $prices[] = [
                        'value' => $priceItem->getValue(),
                        'currency' => $priceItem->getCurrencyId(),
                    ];
                }
            }

            $variants[] = [
                'id' => $variant->getId(),
                'code' => $variant->getCode(),
                'label' => $variant->getLabel(),
                'type' => $variant->getType(),
                'images' => $images,
                'prices' => $prices,
            ];
        }

        return response()->json(['data' => $variants]);
    }

    /**
     * Return autocomplete suggestions for the search bar (products + shops).
     * Lightweight — no pagination, max 5 products + 3 shops.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggest(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim($request->query('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['products' => [], 'shops' => []]);
        }

        $context = app('aimeos.context')->get(false);

        try {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $site        = $siteManager->find('default');
            $siteFilter  = $siteManager->filter(true);
            $siteItems   = $siteManager->search($siteFilter);
            $siteIds     = [];
            foreach ($siteItems as $item) {
                $siteIds[] = $item->getSiteId();
            }
            $sites  = [0 => $site->getSiteId(), 1 => $siteIds, 2 => $site->getSiteId(), 3 => $siteIds];
            $locale = new \Aimeos\MShop\Locale\Item\Standard(['locale.siteid' => $site->getSiteId()], $site, $sites);
            $context->setLocale($locale);
        } catch (\Exception $e) {
            return response()->json(['products' => [], 'shops' => []]);
        }

        // ── Products ──────────────────────────────────────────────────────
        $products = [];
        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            $filter  = $manager->filter(true);
            $filter->add($filter->compare('~=', 'product.label', $query));
            $filter->slice(0, 5);
            $results = $manager->search($filter, ['media', 'price']);

            foreach ($results as $product) {
                $siteDetails = $this->getSiteDetailsFromSiteId($context, $product->getSiteId());

                // Fetch image
                $image = null;
                foreach ($product->getListItems('media', 'default') as $li) {
                    if ($mi = $li->getRefItem()) {
                        $image = $mi->getPreview() ?: $mi->getUrl();
                        break;
                    }
                }

                // Fetch first price
                $price = null;
                foreach ($product->getListItems('price', 'default') as $li) {
                    if ($pi = $li->getRefItem()) {
                        $price = 'Rp ' . number_format((float) $pi->getValue(), 0, ',', '.');
                        break;
                    }
                }

                $products[] = [
                    'id'        => $product->getId(),
                    'label'     => $product->getLabel(),
                    'image'     => $image,
                    'price'     => $price,
                    'shop_name' => $siteDetails['name'],
                    'url'       => url('/products/' . $product->getId()),
                ];
            }
        } catch (\Exception $e) {
            // ignore
        }

        // ── Shops ─────────────────────────────────────────────────────────
        $shops = [];
        try {
            $siteFilter = $siteManager->filter();
            $siteFilter->add($siteFilter->compare('~=', 'locale.site.label', $query));
            $siteFilter->slice(0, 4);
            $foundSites = $siteManager->search($siteFilter);

            foreach ($foundSites as $siteItem) {
                if ($siteItem->getCode() === 'default') {
                    continue;
                }
                $shops[] = [
                    'code'  => $siteItem->getCode(),
                    'name'  => $siteItem->getLabel(),
                    'url'   => url('/shops/' . $siteItem->getCode()),
                ];
                if (count($shops) >= 3) break;
            }
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json(['products' => $products, 'shops' => $shops]);
    }

    /**
     * Resolve site details (label and code) from the siteid path.
     */
    private function getSiteDetailsFromSiteId(\Aimeos\MShop\ContextIface $context, string $siteId): array
    {
        try {
            $manager = \Aimeos\MShop::create($context, 'locale/site');
            $filter  = $manager->filter();
            $parts   = array_filter(explode('.', trim($siteId, '.')));
            $numericId = end($parts);
            if ($numericId) {
                $filter->add($filter->compare('==', 'locale.site.id', (int) $numericId));
                $sites   = $manager->search($filter);
                if (!$sites->isEmpty()) {
                    $site = $sites->first();
                    return [
                        'name' => $site->getLabel(),
                        'code' => $site->getCode()
                    ];
                }
            }
        } catch (\Exception $e) {
            // ignore and fallback
        }
        return ['name' => 'Toko Reborns', 'code' => 'default'];
    }
}

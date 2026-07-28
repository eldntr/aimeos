<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class ShopController
 *
 * Handles shop controller operations for the application.
 */
class ShopController extends Controller
{
    /**
     * Get the Aimeos context. If $siteCode is provided, bootstraps to that site.
     */
    private function getContext(string $siteCode = 'default'): \Aimeos\MShop\ContextIface
    {
        $context = app('aimeos.context')->get(false);
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
        $context->setLocale($locale);

        return $context;
    }

    /**
     * Public profile of a shop (site).
     * @param string $shop_id Expected to be the Aimeos site code (e.g. 'tokobudi')
     */
    public function show(Request $request, string $shop_id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getContext();
        $manager = \Aimeos\MShop::create($context, 'locale/site');
        
        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'locale.site.code', $shop_id));
        $filter->add($filter->compare('==', 'locale.site.status', 1));
        
        $site = $manager->search($filter)->first();
        
        if (!$site) {
            return response()->json(['message' => 'Shop not found.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $site->getId(),
                'code' => $site->getCode(),
                'label' => $site->getLabel(),
                'status' => $site->getStatus(),
                'logo' => $site->getLogo(),
                'config' => $site->getConfig(),
            ]
        ]);
    }

    /**
     * List products from a specific shop.
     */
    public function getProducts(Request $request, string $shop_id): \Illuminate\Http\JsonResponse
    {
        try {
            // Bootstrap context to the specific shop's site code
            $context = $this->getContext($shop_id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Shop not found or unavailable.'], 404);
        }

        $manager = \Aimeos\MShop::create($context, 'product');
        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'product.status', 1));
        
        // Search products belonging to this shop
        $products = $manager->search($filter, ['media', 'price']);

        $data = [];
        foreach ($products as $product) {
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

            $data[] = [
                'id'     => $product->getId(),
                'code'   => $product->getCode(),
                'label'  => $product->getLabel(),
                'type'   => $product->getType(),
                'status' => $product->getStatus(),
                'images' => $images,
                'prices' => $prices,
            ];
        }

        return response()->json(['data' => $data]);
    }
}

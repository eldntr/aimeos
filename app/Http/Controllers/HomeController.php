<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Get promotional banners for the Homepage.
     * We assume banners are products of type 'banner' created by admin.
     */
    public function getBanners(Request $request): \Illuminate\Http\JsonResponse
    {
        // Use default site context
        $context = app('aimeos.context')->get(false);
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = \Aimeos\MShop::create($context, 'product');
        $filter = $manager->filter();
        
        $filter->add($filter->compare('==', 'product.type', 'banner'));
        $filter->add($filter->compare('==', 'product.status', 1));
        
        // Ensure we load the media lists
        $products = $manager->search($filter, ['media']);

        $banners = [];
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

            $banners[] = [
                'id' => $product->getId(),
                'code' => $product->getCode(),
                'label' => $product->getLabel(),
                'images' => $images,
            ];
        }

        return response()->json(['data' => $banners]);
    }
}

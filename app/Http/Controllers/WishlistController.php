<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get initialized Aimeos context with locale
     */
    private function getContextWithLocale()
    {
        $context = app('aimeos.context')->get(false);
        
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $siteItem = $siteManager->create();
        $siteItem->setId('1.');
        $siteItem->setCode('default');
        
        $ref = new \ReflectionClass($localeItem);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($localeItem, $siteItem);
        }
        
        $context->setLocale($localeItem);
        return $context;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $context = $this->getContextWithLocale();
        $listManager = \Aimeos\MShop::create($context, 'customer/lists');
        
        $filter = $listManager->filter();
        $filter->add($filter->and([
            $filter->compare('==', 'customer.lists.parentid', Auth::id()),
            $filter->compare('==', 'customer.lists.domain', 'product'),
            $filter->compare('==', 'customer.lists.type', 'favorite'),
        ]));
        
        $items = $listManager->search($filter);
        
        // Load the products
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getRefId();
        }
        
        $products = [];
        if (!empty($productIds)) {
            $productManager = \Aimeos\MShop::create($context, 'product');
            $pFilter = $productManager->filter();
            $pFilter->add($pFilter->compare('==', 'product.id', $productIds));
            
            $productItems = $productManager->search($pFilter, ['media', 'price', 'text']);
            foreach ($productItems as $productItem) {
                $images = [];
                foreach ($productItem->getListItems('media', 'default') as $listItem) {
                    if ($mediaItem = $listItem->getRefItem()) {
                        $images[] = [
                            'url' => $mediaItem->getUrl(),
                            'preview' => $mediaItem->getPreview(),
                        ];
                    }
                }
                
                $prices = [];
                foreach ($productItem->getListItems('price', 'default') as $listItem) {
                    if ($priceItem = $listItem->getRefItem()) {
                        $prices[] = [
                            'value' => $priceItem->getValue(),
                            'currency' => $priceItem->getCurrencyId(),
                        ];
                    }
                }
                
                $products[] = [
                    'id' => $productItem->getId(),
                    'label' => $productItem->getLabel(),
                    'code' => $productItem->getCode(),
                    'images' => $images,
                    'prices' => $prices,
                ];
            }
        }

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
        ]);

        $context = $this->getContextWithLocale();
        $listManager = \Aimeos\MShop::create($context, 'customer/lists');
        
        // Check if already in wishlist
        $filter = $listManager->filter();
        $filter->add($filter->and([
            $filter->compare('==', 'customer.lists.parentid', Auth::id()),
            $filter->compare('==', 'customer.lists.domain', 'product'),
            $filter->compare('==', 'customer.lists.type', 'favorite'),
            $filter->compare('==', 'customer.lists.refid', $request->product_id),
        ]));
        
        $existing = $listManager->search($filter)->first();
        
        if ($existing) {
            return response()->json([
                'message' => 'Produk sudah ada di wishlist.'
            ], 422);
        }
        
        try {
            $item = $listManager->create();
            $item->setParentId(Auth::id());
            $item->setDomain('product');
            $item->setType('favorite');
            $item->setRefId($request->product_id);
            
            $listManager->save($item);

            return response()->json([
                'message' => 'Produk berhasil ditambahkan ke wishlist.',
                'data' => $item->toArray()
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $context = $this->getContextWithLocale();
        $listManager = \Aimeos\MShop::create($context, 'customer/lists');
        
        $filter = $listManager->filter();
        $filter->add($filter->and([
            $filter->compare('==', 'customer.lists.parentid', Auth::id()),
            $filter->compare('==', 'customer.lists.domain', 'product'),
            $filter->compare('==', 'customer.lists.type', 'favorite'),
            $filter->compare('==', 'customer.lists.refid', $id),
        ]));
        
        $items = $listManager->search($filter);
        $item = $items->first();
        
        if (!$item) {
            return response()->json([
                'message' => 'Produk tidak ditemukan di wishlist.'
            ], 404);
        }
        
        try {
            $listManager->delete($item->getId());

            return response()->json([
                'message' => 'Produk berhasil dihapus dari wishlist.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

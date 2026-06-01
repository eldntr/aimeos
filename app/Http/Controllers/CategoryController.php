<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get the Aimeos default site context.
     */
    private function getContext(Request $request = null): \Aimeos\MShop\ContextIface
    {
        $context = app('aimeos.context')->get(false);
        
        $siteCode = 'default';
        if ($request) {
            $siteCode = $request->query('site') ?: ($request->route('site') ?: 'default');
            if ($siteCode === '1.') {
                $siteCode = 'reborns';
            }
        }
        
        try {
            $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
        } catch (\Exception $e) {
            $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        }
        $context->setLocale($locale);

        return $context;
    }

    /**
     * List product categories (nested/hierarchical).
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $context = $this->getContext($request);
        $manager = \Aimeos\MShop::create($context, 'catalog');

        // Fetch the root node of the catalog tree
        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'catalog.level', 0));
        
        $rootNode = $manager->search($filter)->first();
        if (!$rootNode) {
            return response()->json(['data' => []]);
        }

        // getTree fetches the node and all its children. 
        $tree = $manager->getTree($rootNode->getId(), ['media']);

        return response()->json(['data' => $this->formatCategoryTree($tree->getChildren())]);
    }

    private function formatCategoryTree($tree): array
    {
        $result = [];
        foreach ($tree as $node) {
            $item = [
                'id' => $node->getId(),
                'code' => $node->getCode(),
                'label' => $node->getLabel(),
                'status' => $node->getStatus(),
                'children' => $this->formatCategoryTree($node->getChildren()),
            ];
            $result[] = $item;
        }
        return $result;
    }

    /**
     * List products by category.
     */
    public function getProducts(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getContext($request);
        
        // Ensure catalog exists
        $catalogManager = \Aimeos\MShop::create($context, 'catalog');
        try {
            $catalogManager->get($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        // Fetch catalog/lists to get product IDs
        $listManager = \Aimeos\MShop::create($context, 'catalog/lists');
        $lfilter = $listManager->filter();
        $lfilter->add($lfilter->compare('==', 'catalog.lists.parentid', $id));
        $lfilter->add($lfilter->compare('==', 'catalog.lists.domain', 'product'));
        $listItems = $listManager->search($lfilter);

        $productIds = [];
        foreach ($listItems as $item) {
            $productIds[] = $item->getRefId();
        }

        if (empty($productIds)) {
            return response()->json(['data' => []]);
        }

        $manager = \Aimeos\MShop::create($context, 'product');
        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'product.status', 1));
        $filter->add($filter->compare('in', 'product.id', $productIds));

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

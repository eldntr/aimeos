<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Get the Aimeos context bootstrapped to the seller's own site.
     */
    private function getSellerContext(): \Aimeos\MShop\ContextIface
    {
        $context  = app('aimeos.context')->get(false);
        $user     = Auth::user();
        $siteCode = $this->getSiteCodeFromSiteId($context, $user->siteid);

        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
        $context->setLocale($locale);

        return $context;
    }

    /**
     * Resolve site code from the siteid path stored in users.siteid.
     */
    private function getSiteCodeFromSiteId(\Aimeos\MShop\ContextIface $context, string $siteid): string
    {
        $manager = \Aimeos\MShop::create($context, 'locale/site');
        $filter  = $manager->filter();
        $parts   = array_filter(explode('.', trim($siteid, '.')));
        $numericId = end($parts);
        $filter->add($filter->compare('==', 'locale.site.id', (int) $numericId));
        $sites   = $manager->search($filter);

        if ($sites->isEmpty()) {
            abort(403, 'Seller site not found.');
        }

        return $sites->first()->getCode();
    }

    /**
     * List all categories (catalogs) for this seller.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'catalog');
        $filter  = $manager->filter();

        $catalogs = $manager->search($filter);

        $data = [];
        foreach ($catalogs as $catalog) {
            $data[] = [
                'id' => $catalog->getId(),
                'code' => $catalog->getCode(),
                'label' => $catalog->getLabel(),
                'status' => $catalog->getStatus(),
            ];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new category (catalog).
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'parent_id' => ['sometimes', 'nullable', 'string']
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'catalog');

        $manager->begin();
        try {
            $catalog = $manager->create()
                ->setCode(strip_tags($request->code))
                ->setLabel(strip_tags($request->label))
                ->setStatus($request->input('status', 1));

            if ($request->has('parent_id') && $request->parent_id) {
                // To set a parent, we actually use insert() with the parent ID
                $parent = $manager->get($request->parent_id);
                $manager->insert($catalog, $parent->getId());
            } else {
                // If no parent_id is given, we should probably insert it under the root node
                // We can find the root node (level 0) for the current site
                $filter = $manager->filter()->add(['catalog.level' => 0]);
                $rootNode = $manager->search($filter)->first();
                if ($rootNode) {
                    $manager->insert($catalog, $rootNode->getId());
                } else {
                    $manager->save($catalog);
                }
            }
            
            $manager->commit();

            return response()->json([
                'message' => 'Category created successfully.',
                'data' => [
                    'id' => $catalog->getId(),
                    'code' => $catalog->getCode(),
                    'label' => $catalog->getLabel(),
                    'status' => $catalog->getStatus(),
                ]
            ], 201);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Failed to create category: ' . $e->getMessage()], 500);
        }
    }
}

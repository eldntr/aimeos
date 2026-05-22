<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
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
     * Aimeos stores siteid as a hierarchical path string (e.g. "/1/5/").
     */
    private function getSiteCodeFromSiteId(\Aimeos\MShop\ContextIface $context, string $siteid): string
    {
        $manager = \Aimeos\MShop::create($context, 'locale/site');
        $filter  = $manager->filter();
        // siteid path in Aimeos uses dots (e.g. "1.5.")
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
     * List all products belonging to this seller's site.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');
        $filter  = $manager->filter();

        $products = $manager->search($filter);

        $data = [];
        foreach ($products as $product) {
            $data[] = $this->formatProduct($product);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Show a single product — only if it belongs to this seller's site.
     */
    public function show(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();

        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            $product = $manager->get($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['data' => $this->formatProduct($product)]);
    }

    /**
     * Create a new product in the seller's site.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'label'  => ['required', 'string', 'max:255'],
            'code'   => ['required', 'string', 'max:64'],
            'type'   => ['sometimes', 'string', 'in:default,bundle,select,voucher'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');

        $item = $manager->create()
            ->setLabel(strip_tags($request->label))
            ->setCode(strip_tags($request->code))
            ->setType($request->input('type', 'default'))
            ->setStatus($request->input('status', 1));

        $saved = $manager->save($item);

        return response()->json(['data' => $this->formatProduct($saved)], 201);
    }

    /**
     * Update an existing product — only if it belongs to this seller's site.
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'label'  => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');

        try {
            $product = $manager->get($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        if ($request->has('label')) {
            $product->setLabel(strip_tags($request->label));
        }
        if ($request->has('status')) {
            $product->setStatus((int) $request->status);
        }

        $saved = $manager->save($product);

        return response()->json(['data' => $this->formatProduct($saved)]);
    }

    /**
     * Delete a product — only if it belongs to this seller's site.
     */
    public function destroy(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');

        try {
            $manager->get($id); // ensures it exists within this site context
            $manager->delete($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    /**
     * Format a product item into a plain array for JSON response.
     */
    private function formatProduct(\Aimeos\MShop\Product\Item\Iface $product): array
    {
        return [
            'id'     => $product->getId(),
            'code'   => $product->getCode(),
            'label'  => $product->getLabel(),
            'type'   => $product->getType(),
            'status' => $product->getStatus(),
            'ctime'  => $product->getTimeCreated(),
            'mtime'  => $product->getTimeModified(),
        ];
    }
}

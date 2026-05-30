<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'video' => ['sometimes', 'file', 'mimes:mp4,webm', 'max:51200'], // Max 50MB
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string'],
            'variants' => ['sometimes', 'array'],
            'variants.*.code' => ['required_with:variants', 'string', 'max:64'],
            'variants.*.label' => ['required_with:variants', 'string', 'max:255'],
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');
        
        $manager->begin();
        try {
            $product = $manager->create()
                ->setLabel(strip_tags($request->label))
                ->setCode(strip_tags($request->code))
                ->setType($request->input('type', 'default'))
                ->setStatus($request->input('status', 1));

            $fileService = new \App\Services\FileServerService();
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $mediaUrl = $fileService->uploadFile($file);
                    
                    $mediaItem = $this->createMediaItem($context, $mediaUrl, $file->getMimeType());
                    $listItem = $this->createListItem($context, $mediaItem->getId());
                    
                    $product->addListItem('media', $listItem);
                }
            }
            
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $mediaUrl = $fileService->uploadFile($file);
                
                $mediaItem = $this->createMediaItem($context, $mediaUrl, $file->getMimeType());
                $listItem = $this->createListItem($context, $mediaItem->getId());
                
                $product->addListItem('media', $listItem);
            }
            
            // Add categories
            if ($request->has('categories') && is_array($request->categories)) {
                foreach ($request->categories as $categoryId) {
                    $listItem = $this->createCatalogListItem($context, $categoryId);
                    $product->addListItem('catalog', $listItem);
                }
            }
            
            $saved = $manager->save($product);
            
            // Add variants if type is select
            if ($product->getType() === 'select' && $request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantData) {
                    $variantProduct = $manager->create()
                        ->setLabel(strip_tags($variantData['label']))
                        ->setCode(strip_tags($variantData['code']))
                        ->setType('default')
                        ->setStatus(1);
                    $savedVariant = $manager->save($variantProduct);

                    $listItem = $this->createVariantListItem($context, $savedVariant->getId());
                    $saved->addListItem('product', $listItem);
                }
                $saved = $manager->save($saved); // Save again with attached variants
            }
            

            $manager->commit();
            
            // Trigger kompresi asinkron di file server
            $fileService->triggerCompression();

            return response()->json(['data' => $this->formatProduct($saved)], 201);
            
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal menyimpan produk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing product — only if it belongs to this seller's site.
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'label'  => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string'],
            'variants' => ['sometimes', 'array'],
            'variants.*.code' => ['required_with:variants', 'string', 'max:64'],
            'variants.*.label' => ['required_with:variants', 'string', 'max:255'],
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

        // Update categories if provided (simple append for now)
        if ($request->has('categories') && is_array($request->categories)) {
            // Remove existing catalog lists to replace them
            $listItems = $product->getListItems('catalog', 'default');
            $product->deleteListItems($listItems, 'catalog');
            
            foreach ($request->categories as $categoryId) {
                $listItem = $this->createCatalogListItem($context, $categoryId);
                $product->addListItem('catalog', $listItem);
            }
        }

        $saved = $manager->save($product);
        
        // Add new variants if provided (does not delete existing variants to prevent data loss)
        if ($saved->getType() === 'select' && $request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $variantData) {
                $variantProduct = $manager->create()
                    ->setLabel(strip_tags($variantData['label']))
                    ->setCode(strip_tags($variantData['code']))
                    ->setType('default')
                    ->setStatus(1);
                $savedVariant = $manager->save($variantProduct);

                $listItem = $this->createVariantListItem($context, $savedVariant->getId());
                $saved->addListItem('product', $listItem);
            }
            $saved = $manager->save($saved);
        }



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


    private function createMediaItem(\Aimeos\MShop\ContextIface $context, string $url, string $mimeType): \Aimeos\MShop\Media\Item\Iface
    {
        $mediaManager = \Aimeos\MShop::create($context, 'media');
        $mediaItem = $mediaManager->create()
            ->setDomain('media')
            ->setMimeType($mimeType)
            ->setUrl($url)
            ->setPreview($url)
            ->setStatus(1);
            
        return $mediaManager->save($mediaItem);
    }

    private function createListItem(\Aimeos\MShop\ContextIface $context, string $refId): \Aimeos\MShop\Common\Item\Lists\Iface
    {
        return \Aimeos\MShop::create($context, 'product/lists')->create()
            ->setDomain('media')
            ->setType('default')
            ->setRefId($refId);
    }

    private function createCatalogListItem(\Aimeos\MShop\ContextIface $context, string $catalogId): \Aimeos\MShop\Common\Item\Lists\Iface
    {
        return \Aimeos\MShop::create($context, 'product/lists')->create()
            ->setDomain('catalog')
            ->setType('default')
            ->setRefId($catalogId);
    }

    private function createVariantListItem(\Aimeos\MShop\ContextIface $context, string $variantId): \Aimeos\MShop\Common\Item\Lists\Iface
    {
        return \Aimeos\MShop::create($context, 'product/lists')->create()
            ->setDomain('product')
            ->setType('default')
            ->setRefId($variantId);
    }
}

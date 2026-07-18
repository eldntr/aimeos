<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    use HasSellerContext;


    /**
     * List all products belonging to this seller's site.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');
        $filter  = $manager->filter();

        $products = $manager->search($filter, ['media', 'price', 'catalog', 'text']);

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
            $product = $manager->get($id, ['media', 'price', 'catalog', 'text']);
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
            'price'  => ['sometimes', 'numeric', 'min:0'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:1000000'],
            'description' => ['sometimes', 'string'],
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'video' => ['sometimes', 'file', 'mimes:mp4,webm', 'max:51200'], // Max 50MB
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string'],
            'variants' => ['sometimes', 'array'],
            'variants.*.code' => ['required_with:variants', 'string', 'max:64'],
            'variants.*.label' => ['required_with:variants', 'string', 'max:255'],
            'stock' => ['sometimes', 'integer', 'min:0'],
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

            // Add price
            if ($request->has('price')) {
                $priceManager = \Aimeos\MShop::create($context, 'price');
                $priceItem = $priceManager->create()
                    ->setValue($request->price)
                    ->setCurrencyId('IDR')
                    ->setStatus(1);
                $savedPrice = $priceManager->save($priceItem);
                
                $listItem = \Aimeos\MShop::create($context, 'product/lists')->create()
                    ->setDomain('price')
                    ->setType('default')
                    ->setRefId($savedPrice->getId());
                $product->addListItem('price', $listItem);
            }

            // Add description
            if ($request->has('description') && $request->description) {
                $textManager = \Aimeos\MShop::create($context, 'text');
                $textItem = $textManager->create()
                    ->setType('short')
                    ->setContent(strip_tags($request->description))
                    ->setStatus(1);
                $savedText = $textManager->save($textItem);
                
                $listItem = \Aimeos\MShop::create($context, 'product/lists')->create()
                    ->setDomain('text')
                    ->setType('default')
                    ->setRefId($savedText->getId());
                $product->addListItem('text', $listItem);
            }
            
            $saved = $manager->save($product);

            \Illuminate\Support\Facades\DB::table('mshop_product')
                ->where('id', $saved->getId())
                ->update(['weight_grams' => (int) $request->weight_grams]);
            
            // Create stock entry for product
            $this->saveStockItem($context, $saved->getId());
            
            // Also add inverse mapping in catalog/lists for categories
            if ($request->has('categories') && is_array($request->categories)) {
                try {
                    $catalogListManager = \Aimeos\MShop::create($context, 'catalog/lists');
                    foreach ($request->categories as $categoryId) {
                        $catalogListItem = $catalogListManager->create()
                            ->setParentId($categoryId)
                            ->setDomain('product')
                            ->setRefId($saved->getId())
                            ->setType('default')
                            ->setStatus(1);
                        $catalogListManager->save($catalogListItem);
                    }
                } catch (\Exception $e) {
                    // ignore if failed
                }
            }
            
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

                    // Create stock entry for variant product
                    $this->saveStockItem($context, $savedVariant->getId());
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
            'price'  => ['sometimes', 'numeric', 'min:0'],
            'weight_grams' => ['sometimes', 'integer', 'min:1', 'max:1000000'],
            'description' => ['sometimes', 'string'],
            'images' => ['sometimes', 'array', 'max:5'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string'],
            'variants' => ['sometimes', 'array'],
            'variants.*.code' => ['required_with:variants', 'string', 'max:64'],
            'variants.*.label' => ['required_with:variants', 'string', 'max:255'],
            'stock' => ['sometimes', 'integer', 'min:0'],
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');

        try {
            $product = $manager->get($id, ['media', 'price', 'catalog', 'text']);
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
            $product->deleteListItems($listItems);
            
            foreach ($request->categories as $categoryId) {
                $listItem = $this->createCatalogListItem($context, $categoryId);
                $product->addListItem('catalog', $listItem);
            }

            // Also remove existing inverse mappings in catalog/lists
            try {
                $catalogListManager = \Aimeos\MShop::create($context, 'catalog/lists');
                $clFilter = $catalogListManager->filter();
                $clFilter->add($clFilter->compare('==', 'catalog.lists.refid', $product->getId()));
                $clFilter->add($clFilter->compare('==', 'catalog.lists.domain', 'product'));
                $oldCatalogLists = $catalogListManager->search($clFilter);
                $catalogListManager->delete($oldCatalogLists);
                
                // Add new ones
                foreach ($request->categories as $categoryId) {
                    $catalogListItem = $catalogListManager->create()
                        ->setParentId($categoryId)
                        ->setDomain('product')
                        ->setRefId($product->getId())
                        ->setType('default')
                        ->setStatus(1);
                    $catalogListManager->save($catalogListItem);
                }
            } catch (\Exception $e) {
                // ignore if failed
            }
        }

        // Update price
        if ($request->has('price')) {
            $listItems = $product->getListItems('price', 'default');
            $product->deleteListItems($listItems, true);
            
            $priceManager = \Aimeos\MShop::create($context, 'price');
            $priceItem = $priceManager->create()
                ->setValue($request->price)
                ->setCurrencyId('IDR')
                ->setStatus(1);
            $savedPrice = $priceManager->save($priceItem);
            
            $listItem = \Aimeos\MShop::create($context, 'product/lists')->create()
                ->setDomain('price')
                ->setType('default')
                ->setRefId($savedPrice->getId());
            $product->addListItem('price', $listItem);
        }

        // Update description
        if ($request->has('description')) {
            $listItems = $product->getListItems('text', 'default');
            $product->deleteListItems($listItems, true);
            
            if ($request->description) {
                $textManager = \Aimeos\MShop::create($context, 'text');
                $textItem = $textManager->create()
                    ->setType('short')
                    ->setContent(strip_tags($request->description))
                    ->setStatus(1);
                $savedText = $textManager->save($textItem);
                
                $listItem = \Aimeos\MShop::create($context, 'product/lists')->create()
                    ->setDomain('text')
                    ->setType('default')
                    ->setRefId($savedText->getId());
                $product->addListItem('text', $listItem);
            }
        }

        // Update images if provided
        if ($request->hasFile('images')) {
            $listItems = $product->getListItems('media', 'default');
            $product->deleteListItems($listItems, true);
            
            $fileService = new \App\Services\FileServerService();
            foreach ($request->file('images') as $file) {
                $mediaUrl = $fileService->uploadFile($file);
                
                $mediaItem = $this->createMediaItem($context, $mediaUrl, $file->getMimeType());
                $listItem = $this->createListItem($context, $mediaItem->getId());
                
                $product->addListItem('media', $listItem);
            }
            $fileService->triggerCompression();
        }

        $saved = $manager->save($product);

        if ($request->has('weight_grams')) {
            \Illuminate\Support\Facades\DB::table('mshop_product')
                ->where('id', $saved->getId())
                ->update(['weight_grams' => (int) $request->weight_grams]);
        }
        
        // Ensure stock entry exists for product
        $this->saveStockItem($context, $saved->getId(), $request->input('stock'));
        
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

                // Create stock entry for variant product
                $this->saveStockItem($context, $savedVariant->getId());
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

            // Remove inverse mappings in catalog/lists
            try {
                $catalogListManager = \Aimeos\MShop::create($context, 'catalog/lists');
                $clFilter = $catalogListManager->filter();
                $clFilter->add($clFilter->compare('==', 'catalog.lists.refid', $id));
                $clFilter->add($clFilter->compare('==', 'catalog.lists.domain', 'product'));
                $oldCatalogLists = $catalogListManager->search($clFilter);
                $catalogListManager->delete($oldCatalogLists);
            } catch (\Exception $ex) {
                // ignore
            }

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
        $images = [];
        try {
            foreach ($product->getListItems('media', 'default') as $listItem) {
                if ($mediaItem = $listItem->getRefItem()) {
                    $images[] = [
                        'id' => $mediaItem->getId(),
                        'url' => $mediaItem->getUrl(),
                        'preview' => $mediaItem->getPreview(),
                        'position' => $listItem->getPosition(),
                    ];
                }
            }
            // Sort by position ascending
            usort($images, function($a, $b) {
                return $a['position'] <=> $b['position'];
            });
        } catch (\Exception $e) {
            // ignore if not loaded
        }

        $prices = [];
        try {
            foreach ($product->getListItems('price', 'default') as $listItem) {
                if ($priceItem = $listItem->getRefItem()) {
                    $prices[] = [
                        'value' => $priceItem->getValue(),
                        'currency' => $priceItem->getCurrencyId(),
                    ];
                }
            }
        } catch (\Exception $e) {
            // ignore if not loaded
        }

        $priceLabel = '-';
        $priceRaw = 0;
        if (!empty($prices)) {
            $firstPrice = $prices[0];
            $priceRaw = $firstPrice['value'];
            $priceLabel = 'Rp ' . number_format($firstPrice['value'], 0, ',', '.');
        }

        $categoryIds = [];
        try {
            foreach ($product->getListItems('catalog', 'default') as $listItem) {
                $categoryIds[] = $listItem->getRefId();
            }
        } catch (\Exception $e) {
            // ignore if not loaded
        }

        $description = '';
        try {
            foreach ($product->getListItems('text', 'default') as $listItem) {
                if ($textItem = $listItem->getRefItem()) {
                    if ($textItem->getType() === 'short') {
                        $description = $textItem->getContent();
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // ignore if not loaded
        }

        $stockLevel = 0;
        try {
            $stockManager = \Aimeos\MShop::create($product->getContext(), 'stock');
            $filter = $stockManager->filter();
            $filter->add($filter->compare('==', 'stock.productid', $product->getId()));
            $stocks = $stockManager->search($filter);
            if ($stockItem = $stocks->first()) {
                $stockLevel = $stockItem->getStockLevel();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'id'          => $product->getId(),
            'code'        => $product->getCode(),
            'label'       => $product->getLabel(),
            'name'        => $product->getLabel(), // alias for compatibility
            'type'        => $product->getType(),
            'status'      => $product->getStatus(),
            'ctime'       => $product->getTimeCreated(),
            'mtime'       => $product->getTimeModified(),
            'images'      => $images,
            'image'       => $images[0]['url'] ?? null,
            'prices'      => $prices,
            'price'       => $priceLabel,
            'priceRaw'    => $priceRaw,
            'categories'  => $categoryIds,
            'description' => $description,
            'stock'       => $stockLevel,
            'weight_grams' => \Illuminate\Support\Facades\Schema::hasColumn('mshop_product', 'weight_grams')
                ? (int) (\Illuminate\Support\Facades\DB::table('mshop_product')->where('id', $product->getId())->value('weight_grams') ?: 1000)
                : 1000,
            'rating'      => '-',
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
    public function getVariants(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        
        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            $product = $manager->get($id, ['product']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $variants = [];
        foreach ($product->getListItems('product', 'default') as $listItem) {
            try {
                $variantProd = $manager->get($listItem->getRefId());
                $variants[] = [
                    'list_id' => $listItem->getId(),
                    'variant' => $this->formatProduct($variantProd)
                ];
            } catch (\Exception $e) {
                // skip if not found
            }
        }

        return response()->json(['data' => $variants]);
    }

    public function addVariant(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');

        $manager->begin();
        try {
            $parentProduct = $manager->get($id, ['product']);
            
            // If parent product was default, change to select
            if ($parentProduct->getType() === 'default') {
                $parentProduct->setType('select');
                $manager->save($parentProduct);
            }

            $variantProduct = $manager->create()
                ->setLabel(strip_tags($request->label))
                ->setCode(strip_tags($request->code))
                ->setType('default')
                ->setStatus(1);
            $savedVariant = $manager->save($variantProduct);

            $listItem = $this->createVariantListItem($context, $savedVariant->getId());
            $parentProduct->addListItem('product', $listItem);
            $manager->save($parentProduct);

            $manager->commit();

            return response()->json([
                'message' => 'Varian berhasil ditambahkan.',
                'data' => $this->formatProduct($savedVariant)
            ], 201);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function deleteVariant(Request $request, string $id, string $variant_id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');
        
        $manager->begin();
        try {
            $parentProduct = $manager->get($id, ['product']);
            
            // Find the list item connecting the variant
            $listItems = $parentProduct->getListItems('product', 'default');
            $foundListItem = null;
            foreach ($listItems as $item) {
                if ($item->getRefId() === $variant_id) {
                    $foundListItem = $item;
                    break;
                }
            }
            
            if (!$foundListItem) {
                return response()->json(['message' => 'Variant list item not found in this product.'], 404);
            }
            
            $parentProduct->deleteListItem('product', $foundListItem);
            $manager->save($parentProduct);
            
            // Delete the variant product itself
            $manager->delete($variant_id);
            
            $manager->commit();
            return response()->json(['message' => 'Varian berhasil dihapus.']);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getImages(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        
        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            $product = $manager->get($id, ['media']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $mediaManager = \Aimeos\MShop::create($context, 'media');
        $images = [];
        
        foreach ($product->getListItems('media', 'default') as $listItem) {
            try {
                $media = $mediaManager->get($listItem->getRefId());
                $images[] = [
                    'list_id' => $listItem->getId(),
                    'media_id' => $media->getId(),
                    'url' => $media->getUrl(),
                    'mimetype' => $media->getMimeType()
                ];
            } catch (\Exception $e) {
                // skip
            }
        }

        return response()->json(['data' => $images]);
    }

    public function uploadImages(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');

        $manager->begin();
        try {
            $product = $manager->get($id, ['media']);
            $fileService = new \App\Services\FileServerService();
            $uploaded = [];

            foreach ($request->file('images') as $file) {
                $mediaUrl = $fileService->uploadFile($file);
                
                $mediaItem = $this->createMediaItem($context, $mediaUrl, $file->getMimeType());
                $listItem = $this->createListItem($context, $mediaItem->getId());
                
                $product->addListItem('media', $listItem);
                $uploaded[] = $mediaUrl;
            }
            
            $manager->save($product);
            $manager->commit();
            $fileService->triggerCompression();

            return response()->json([
                'message' => 'Gambar berhasil diunggah.',
                'data' => $uploaded
            ], 201);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function deleteImage(Request $request, string $id, string $media_id): \Illuminate\Http\JsonResponse
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');
        $mediaManager = \Aimeos\MShop::create($context, 'media');
        
        $manager->begin();
        try {
            $product = $manager->get($id, ['media']);
            
            $listItems = $product->getListItems('media', 'default');
            $foundListItem = null;
            foreach ($listItems as $item) {
                if ($item->getRefId() === $media_id) {
                    $foundListItem = $item;
                    break;
                }
            }
            
            if (!$foundListItem) {
                return response()->json(['message' => 'Media list item not found in this product.'], 404);
            }
            
            $product->deleteListItem('media', $foundListItem);
            $manager->save($product);
            
            $mediaManager->delete($media_id);
            
            $manager->commit();
            return response()->json(['message' => 'Gambar berhasil dihapus.']);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reorderImages(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'string'
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'product');
        
        $manager->begin();
        try {
            $product = $manager->get($id, ['media']);
            $listItems = $product->getListItems('media', 'default');
            
            $idMap = [];
            foreach ($listItems as $item) {
                $idMap[$item->getRefId()] = $item;
            }
            
            foreach ($request->image_ids as $pos => $mediaId) {
                if (isset($idMap[$mediaId])) {
                    $idMap[$mediaId]->setPosition($pos);
                }
            }
            
            $manager->save($product);
            $manager->commit();
            return response()->json(['message' => 'Urutan gambar berhasil diperbarui.']);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Create or update a stock entry to ensure product has stock (null = unlimited stock)
     */
    private function saveStockItem(\Aimeos\MShop\ContextIface $context, string $productId, ?int $stockLevel = null): void
    {
        try {
            $stockManager = \Aimeos\MShop::create($context, 'stock');
            
            $filter = $stockManager->filter();
            $filter->add($filter->compare('==', 'stock.productid', $productId));
            $stocks = $stockManager->search($filter);
            
            $stockItem = $stocks->first() ?: $stockManager->create();
            $stockItem->setProductId($productId)
                ->setStockLevel($stockLevel)
                ->setType('default');
            
            $stockManager->save($stockItem);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menyimpan stock: ' . $e->getMessage());
        }
    }
}

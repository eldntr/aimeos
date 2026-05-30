<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;

class BannerController extends Controller
{
    public function index()
    {
        $context = app('aimeos.context')->get(false);
        // Default site context for master banners
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = MShop::create($context, 'product');
        $filter = $manager->filter();
        
        $filter->add($filter->compare('==', 'product.type', 'banner'));
        
        $products = $manager->search($filter, ['media']);

        $banners = [];
        foreach ($products as $product) {
            $images = [];
            foreach ($product->getListItems('media', 'default') as $listItem) {
                if ($mediaItem = $listItem->getRefItem()) {
                    $images[] = [
                        'id' => $mediaItem->getId(),
                        'url' => $mediaItem->getUrl()
                    ];
                }
            }

            $banners[] = [
                'id' => $product->getId(),
                'code' => $product->getCode(),
                'label' => $product->getLabel(),
                'status' => $product->getStatus(),
                'images' => $images,
            ];
        }

        return response()->json(['data' => $banners]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean'
        ]);

        $context = app('aimeos.context')->get(false);
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = MShop::create($context, 'product');
        
        try {
            $manager->begin();
            
            $item = $manager->create();
            $code = 'banner-' . uniqid();
            $item->setType('banner');
            $item->setCode($code);
            $item->setLabel($request->label);
            $item->setStatus($request->input('status', 1));
            
            $saved = $manager->save($item);
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('banners', 'public');
                $url = asset('storage/' . $path);
                
                $mediaManager = MShop::create($context, 'media');
                $mediaItem = $mediaManager->create()
                    ->setDomain('media')
                    ->setMimeType($file->getMimeType())
                    ->setUrl($url)
                    ->setPreview($url)
                    ->setStatus(1);
                    
                $savedMedia = $mediaManager->save($mediaItem);
                
                $listItem = MShop::create($context, 'product/lists')->create()
                    ->setDomain('media')
                    ->setType('default')
                    ->setRefId($savedMedia->getId());
                    
                $saved->addListItem('media', $listItem);
                $manager->save($saved);
            }
            
            $manager->commit();
            
            return response()->json([
                'message' => 'Banner berhasil ditambahkan.',
                'data' => [
                    'id' => $saved->getId(),
                    'label' => $saved->getLabel()
                ]
            ], 201);
            
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal menambahkan banner.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $context = app('aimeos.context')->get(false);
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = MShop::create($context, 'product');

        try {
            $item = $manager->get($id, ['media']);
            
            if ($item->getType() !== 'banner') {
                return response()->json(['message' => 'Produk bukan tipe banner.'], 400);
            }
            
            $manager->begin();
            
            // Delete associated media items
            $mediaManager = MShop::create($context, 'media');
            foreach ($item->getListItems('media', 'default') as $listItem) {
                if ($mediaItem = $listItem->getRefItem()) {
                    $mediaManager->delete($mediaItem);
                }
            }
            
            $manager->delete($item);
            $manager->commit();
            
            return response()->json(['message' => 'Banner berhasil dihapus.']);
            
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Banner tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal menghapus banner.', 'error' => $e->getMessage()], 500);
        }
    }
}

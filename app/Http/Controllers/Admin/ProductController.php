<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;

class ProductController extends Controller
{
    /**
     * Get a list of all products in the marketplace for moderation
     */
    public function index(Request $request)
    {
        $context = app('aimeos.context')->get(false);
        $locale = MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = MShop::create($context, 'product');
        $filter = $manager->filter();
        
        // Exclude banners
        $filter->add($filter->compare('!=', 'product.type', 'banner'));

        // Search text filter
        $search = $request->query('search');
        if ($search) {
            $filter->add($filter->compare('==', 'product.label', '%' . $search . '%'));
        }
        
        // Search items with media reference
        $products = $manager->search($filter, ['media']);

        // Build a mapping of siteid to merchant/store name
        $merchantMap = \App\Models\User::whereNotNull('siteid')
            ->get()
            ->pluck('name', 'siteid')
            ->toArray();

        $list = [];
        foreach ($products as $product) {
            $images = [];
            foreach ($product->getListItems('media', 'default') as $listItem) {
                if ($mediaItem = $listItem->getRefItem()) {
                    $images[] = $mediaItem->getUrl();
                }
            }

            $siteId = $product->getSiteId();
            $merchantName = isset($merchantMap[$siteId]) ? $merchantMap[$siteId] : 'Platform Utama';

            $list[] = [
                'id' => $product->getId(),
                'code' => $product->getCode(),
                'label' => $product->getLabel(),
                'status' => $product->getStatus(),
                'type' => $product->getType(),
                'image' => count($images) > 0 ? $images[0] : null,
                'merchant_name' => $merchantName,
            ];
        }

        return response()->json(['data' => $list]);
    }

    /**
     * Disable/Ban a violating product and warn the seller
     */
    public function ban(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $context = app('aimeos.context')->get(false);
        $locale = MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = MShop::create($context, 'product');

        try {
            $item = $manager->get($id);
            $manager->begin();
            
            // Toggle/Set status to 0 (disabled/banned)
            $item->setStatus(0);
            $manager->save($item);
            $manager->commit();

            // Find seller and send notification
            $siteId = $item->getSiteId();
            $seller = \App\Models\User::where('siteid', $siteId)->first();
            if ($seller) {
                $seller->notify(new \App\Notifications\SellerWarning($item->getLabel(), strip_tags($request->reason)));
            }

            return response()->json([
                'message' => 'Produk berhasil dinonaktifkan (Banned) dan surat peringatan telah dikirim ke merchant.'
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal memproses moderasi.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a violating product from the marketplace
     */
    public function destroy($id)
    {
        $context = app('aimeos.context')->get(false);
        $locale = MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $manager = MShop::create($context, 'product');

        try {
            $item = $manager->get($id, ['media']);
            
            $manager->begin();
            
            // Delete associated media items first to prevent orphans
            $mediaManager = MShop::create($context, 'media');
            foreach ($item->getListItems('media', 'default') as $listItem) {
                if ($mediaItem = $listItem->getRefItem()) {
                    $mediaManager->delete($mediaItem);
                }
            }
            
            $manager->delete($item);
            $manager->commit();
            
            return response()->json(['message' => 'Produk berhasil dihapus dari marketplace (moderasi sukses).']);
            
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal menghapus produk.', 'error' => $e->getMessage()], 500);
        }
    }
}

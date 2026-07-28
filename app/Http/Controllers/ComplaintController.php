<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FileServerService;
use Aimeos\MShop\Order\Item\Base as OrderBase;

/**
 * Class ComplaintController
 *
 * Handles complaint controller operations for the application.
 */
class ComplaintController extends Controller
{
    /**
     * Get initialized Aimeos context with locale
     */
    private function getContextWithLocale()
    {
        $context = app('aimeos.context')->get(false);
        
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', 'IDR', false);
        
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        
        try {
            $site = $siteManager->find('default');
            $siteFilter = $siteManager->filter(true);
            $siteItems = $siteManager->search($siteFilter);
            $siteIds = [];
            foreach ($siteItems as $item) {
                $siteIds[] = $item->getSiteId();
            }
            
            $sites = [
                0 => $site->getSiteId(),
                1 => $siteIds,
                2 => $site->getSiteId(),
                3 => $siteIds
            ];
            $localeItem = new \Aimeos\MShop\Locale\Item\Standard($localeItem->toArray(), $site, $sites);
        } catch (\Exception $e) {
            $siteItem = $siteManager->create();
            $siteItem->setId('1.');
            $siteItem->setCode('default');
            
            $ref = new \ReflectionClass($localeItem);
            if ($ref->hasProperty('siteItem')) {
                $prop = $ref->getProperty('siteItem');
                $prop->setAccessible(true);
                $prop->setValue($localeItem, $siteItem);
            }
        }
        
        $context->setLocale($localeItem);
        return $context;
    }

    /**
     * File a complaint for an order.
     */
    public function store(Request $request, $orderId, FileServerService $fileServerService)
    {
        $request->validate([
            'complaint' => 'required|string',
            'requested_resolution' => 'required|in:return_refund,full_refund,partial_refund',
            'requested_refund_percent' => 'nullable|required_if:requested_resolution,partial_refund|integer|min:1|max:99',
            'proof_photo' => 'required|image|max:5120', // max 5MB
            'unboxing_video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm|max:51200', // max 50MB
        ]);

        $context = $this->getContextWithLocale();
        $user = Auth::user();

        // 1. Verify if the order belongs to the user
        $orderManager = \Aimeos\MShop::create($context, 'order');
        try {
            $order = $orderManager->get($orderId);
            if ($order->getCustomerId() !== (string)$user->id) {
                return response()->json(['message' => 'Unauthorized order access.'], 403);
            }
            if ($order->getStatusDelivery() !== OrderBase::STAT_DISPATCHED) {
                return response()->json(['message' => 'Komplain hanya bisa diajukan saat pesanan dalam pengiriman.'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // 2. Handle required evidence uploads via FileServerService
        $proofPhotoUrl = null;
        $unboxingVideoUrl = null;
        try {
            if ($request->hasFile('proof_photo')) {
                $proofPhotoUrl = $fileServerService->uploadFile($request->file('proof_photo'));
            }
            if ($request->hasFile('unboxing_video')) {
                $unboxingVideoUrl = $fileServerService->uploadFile($request->file('unboxing_video'));
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengunggah bukti komplain: ' . $e->getMessage()], 422);
        }

        // 3. Save complaint using Aimeos Review manager (domain: order)
        $reviewManager = \Aimeos\MShop::create($context, 'review');
        
        try {
            $item = $reviewManager->create();
            $item->setDomain('order');
            $item->setRefId($orderId);
            $item->setCustomerId($user->id);
            $item->setName($user->name);
            $item->setRating(1); // Default rating for complaint
            
            $comment = $request->input('complaint');
            $comment .= "\n\n[Requested Resolution: " . $request->input('requested_resolution') . "]";
            if ($request->input('requested_resolution') === 'partial_refund') {
                $comment .= "\n[Requested Refund Percent: " . (int) $request->input('requested_refund_percent') . "]";
            }
            if ($proofPhotoUrl) {
                $comment .= "\n\n[Proof Photo: " . $proofPhotoUrl . "]";
            }
            if ($unboxingVideoUrl) {
                $comment .= "\n\n[Unboxing Video: " . $unboxingVideoUrl . "]";
            }
            $item->setComment($comment);
            $item->setStatus(1); // Active
            
            $reviewManager->save($item);

            return response()->json([
                'message' => 'Komplain berhasil diajukan.',
                'data' => $item->toArray()
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Seller responds to a complaint.
     */
    public function resolve(Request $request, $orderId)
    {
        $request->validate([
            'resolution' => 'required|string',
        ]);

        $context = $this->getContextWithLocale();
        // Assume Seller/Admin authorization is handled by middleware
        
        $reviewManager = \Aimeos\MShop::create($context, 'review');
        
        try {
            // Find the complaint for this order
            $filter = $reviewManager->filter();
            $filter->add($filter->and([
                $filter->compare('==', 'review.domain', 'order'),
                $filter->compare('==', 'review.refid', $orderId)
            ]));
            
            $items = $reviewManager->search($filter);
            
            if (count($items) === 0) {
                return response()->json(['message' => 'Complaint not found for this order.'], 404);
            }
            
            $complaintItem = reset($items); // Get the first complaint
            $complaintItem->setResponse($request->input('resolution'));
            
            $reviewManager->save($complaintItem);

            return response()->json([
                'message' => 'Resolusi komplain berhasil disimpan.',
                'data' => $complaintItem->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

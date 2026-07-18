<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FileServerService;

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
            'proof' => 'required|image|max:5120', // max 5MB
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // 2. Handle Proof Photo Upload via FileServerService
        $proofUrl = null;
        try {
            if ($request->hasFile('proof')) {
                $proofUrl = $fileServerService->uploadFile($request->file('proof'));
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload proof: ' . $e->getMessage()], 422);
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
            if ($proofUrl) {
                $comment .= "\n\n[Proof of Complaint: " . $proofUrl . "]";
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

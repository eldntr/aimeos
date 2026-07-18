<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Aimeos\MShop;

class ComplaintModerationController extends Controller
{
    /**
     * Get initialized Aimeos context with locale
     */
    private function getContextWithLocale()
    {
        $context = app('aimeos.context')->get(false);
        $localeManager = MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', 'IDR', false);
        
        $siteManager = MShop::create($context, 'locale/site');
        
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
            // fallback
        }
        
        $context->setLocale($localeItem);
        return $context;
    }

    /**
     * Get all marketplace complaints / disputes for admin resolution
     */
    public function getDisputes()
    {
        $context = $this->getContextWithLocale();
        $reviewManager = MShop::create($context, 'review');
        
        $filter = $reviewManager->filter();
        $filter->add($filter->compare('==', 'review.domain', 'order'));
        
        $complaints = $reviewManager->search($filter);
        
        $orderIds = [];
        foreach ($complaints as $item) {
            $orderIds[] = $item->getRefId();
        }

        // Fetch orders siteid mappings
        $orderSiteMap = DB::table('mshop_order')
            ->whereIn('id', $orderIds)
            ->pluck('siteid', 'id')
            ->toArray();

        $merchantMap = \App\Models\User::whereNotNull('siteid')
            ->get()
            ->pluck('name', 'siteid')
            ->toArray();

        $list = [];
        foreach ($complaints as $item) {
            $commentText = $item->getComment();
            $proofUrl = null;
            
            // Extract proof URL if it exists
            if (preg_match('/\[Proof of Complaint:\s*([^\]]+)\]/', $commentText, $matches)) {
                $proofUrl = trim($matches[1]);
                $commentText = trim(preg_replace('/\[Proof of Complaint:\s*[^\]]+\]/', '', $commentText));
            }

            $orderId = $item->getRefId();
            $orderSiteId = isset($orderSiteMap[$orderId]) ? $orderSiteMap[$orderId] : null;
            $merchantName = isset($merchantMap[$orderSiteId]) ? $merchantMap[$orderSiteId] : 'Platform Utama';

            $list[] = [
                'id' => $item->getId(),
                'order_id' => $orderId,
                'customer_name' => $item->getName(),
                'customer_id' => $item->getCustomerId(),
                'merchant_name' => $merchantName,
                'complaint' => $commentText,
                'proof_url' => $proofUrl,
                'seller_response' => $item->getResponse() ?: 'Belum ada tanggapan dari merchant.',
                'status' => $item->getStatus(),
                'created_at' => $item->getTimeCreated()
            ];
        }

        return response()->json([
            'message' => 'Daftar sengketa berhasil diambil.',
            'data' => $list
        ]);
    }

    /**
     * Resolve a dispute (dummy escrow resolution)
     */
    public function resolveDispute(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:refund,release'
        ]);

        $context = $this->getContextWithLocale();
        $reviewManager = MShop::create($context, 'review');

        try {
            $complaintItem = $reviewManager->get($id);
            
            // Perform logic
            $decisionText = $request->decision === 'refund' 
                ? 'Dana berhasil di-refund ke Saldo Pembeli (Escrow refunded).' 
                : 'Dana berhasil diteruskan ke Rekening Dompet Penjual (Escrow released).';

            // Mark complaint as resolved (status = 0 or special tag)
            $complaintItem->setStatus(0); // Deactivate/Resolve
            $complaintItem->setResponse($complaintItem->getResponse() . "\n\n[ADMIN RESOLUTION: " . strtoupper($request->decision) . " - " . $decisionText . "]");
            $reviewManager->save($complaintItem);

            return response()->json([
                'message' => 'Sengketa berhasil diselesaikan. ' . $decisionText,
                'data' => [
                    'id' => $complaintItem->getId(),
                    'order_id' => $complaintItem->getRefId(),
                    'decision' => $request->decision
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses sengketa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all orders in e-commerce marketplace for tracking
     */
    public function getOrders()
    {
        // Fetch directly from mshop_order table for lightning fast query and reliability
        $orders = DB::table('mshop_order')
            ->orderBy('ctime', 'desc')
            ->get(['id', 'customerid', 'price', 'statuspayment', 'statusdelivery', 'ctime', 'comment', 'siteid']);

        $globalCommissionRate = (float) \App\Models\SystemSetting::getVal('platform_commission', 5.0);

        $userMap = \App\Models\User::pluck('name', 'id')->toArray();
        $merchantMap = \App\Models\User::whereNotNull('siteid')
            ->get()
            ->pluck('name', 'siteid')
            ->toArray();

        $list = [];
        foreach ($orders as $order) {
            $paymentStatusText = $this->getPaymentStatusText($order->statuspayment);
            $deliveryStatusText = $this->getDeliveryStatusText($order->statusdelivery);

            // Dynamically calculate platform commission and seller revenue share
            $platformFee = ($order->price * $globalCommissionRate) / 100;
            $sellerShare = $order->price - $platformFee;

            $customerName = isset($userMap[$order->customerid]) ? $userMap[$order->customerid] : 'Guest / Pelanggan';
            $merchantName = isset($merchantMap[$order->siteid]) ? $merchantMap[$order->siteid] : 'Platform Utama';

            $list[] = [
                'id' => $order->id,
                'customer_id' => $order->customerid,
                'customer_name' => $customerName,
                'merchant_name' => $merchantName,
                'price' => (float)$order->price,
                'commission_rate' => $globalCommissionRate,
                'platform_commission_fee' => $platformFee,
                'seller_share' => $sellerShare,
                'payment_status' => $paymentStatusText,
                'delivery_status' => $deliveryStatusText,
                'created_at' => $order->ctime,
                'comment' => $order->comment
            ];
        }

        return response()->json([
            'message' => 'Daftar pesanan global berhasil diambil.',
            'data' => $list
        ]);
    }

    private function getPaymentStatusText($code)
    {
        switch ($code) {
            case -1: return 'Dibatalkan';
            case 0: return 'Belum Bayar';
            case 1: return 'Menunggu Pembayaran';
            case 2: return 'Pembayaran Diterima (Escrow)';
            case 3: return 'Pembayaran Berhasil';
            case 4: return 'Refunded';
            default: return 'Pending';
        }
    }

    private function getDeliveryStatusText($code)
    {
        switch ($code) {
            case -1: return 'Gagal Kirim';
            case 0: return 'Belum Diproses';
            case 1: return 'Sedang Dipacking';
            case 2: return 'Dalam Pengiriman';
            case 3: return 'Telah Sampai';
            case 4: return 'Selesai';
            default: return 'Pending';
        }
    }
}

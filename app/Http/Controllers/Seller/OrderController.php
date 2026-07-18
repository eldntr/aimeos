<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;
use App\Notifications\OrderStatusUpdated;

class OrderController extends Controller
{
    use HasSellerContext;

    private function getOrderContext()
    {
        $context = app('aimeos.context')->get(false);
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);

        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $siteItem = $siteManager->create();
        $siteItem->setId('1.');
        $siteItem->setCode('default');

        $ref = new \ReflectionClass($localeItem);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($localeItem, $siteItem);
        }

        $context->setLocale($localeItem);
        return $context;
    }

    private function getContextForOrder(string $orderId)
    {
        $siteId = \DB::table('mshop_order')->where('id', $orderId)->value('siteid');

        return $siteId === $this->sellerSiteId()
            ? $this->getSellerContext()
            : $this->getOrderContext();
    }

    private function getCommissionRate(): float
    {
        return (float) \App\Models\SystemSetting::getVal('platform_commission', 5.0);
    }

    private function getSellerShare(float $grossAmount): float
    {
        $commissionRate = $this->getCommissionRate();
        return $grossAmount - (($grossAmount * $commissionRate) / 100);
    }

    private function sellerSiteId(): ?string
    {
        return auth()->user()?->siteid;
    }

    private function sellerOwnsOrder(string $orderId): bool
    {
        return \DB::table('mshop_order_product')
            ->join('mshop_product', 'mshop_order_product.prodid', '=', 'mshop_product.id')
            ->where('mshop_order_product.parentid', $orderId)
            ->where('mshop_product.siteid', $this->sellerSiteId())
            ->exists();
    }

    private function getSellerOrderProductTotal(string $orderId): float
    {
        return (float) \DB::table('mshop_order_product')
            ->join('mshop_product', 'mshop_order_product.prodid', '=', 'mshop_product.id')
            ->where('mshop_order_product.parentid', $orderId)
            ->where('mshop_product.siteid', $this->sellerSiteId())
            ->selectRaw('COALESCE(SUM(mshop_order_product.price * mshop_order_product.quantity), 0) as total')
            ->value('total');
    }

    private function getSellerOrderIds(): array
    {
        return \DB::table('mshop_order')
            ->join('mshop_order_product', 'mshop_order.id', '=', 'mshop_order_product.parentid')
            ->join('mshop_product', 'mshop_order_product.prodid', '=', 'mshop_product.id')
            ->where('mshop_product.siteid', $this->sellerSiteId())
            ->where('mshop_order.statuspayment', '>', Base::PAY_UNFINISHED)
            ->groupBy('mshop_order.id')
            ->orderByDesc(\DB::raw('MAX(mshop_order.ctime)'))
            ->limit(50)
            ->pluck('mshop_order.id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    private function productBelongsToSeller($product): bool
    {
        return \DB::table('mshop_product')
            ->where('id', $product->getProductId())
            ->where('siteid', $this->sellerSiteId())
            ->exists();
    }

    private function getProductImage(?string $productId): ?string
    {
        if (!$productId) {
            return null;
        }

        $media = \DB::table('mshop_product_list')
            ->join('mshop_media', 'mshop_product_list.refid', '=', 'mshop_media.id')
            ->where('mshop_product_list.parentid', $productId)
            ->where('mshop_product_list.domain', 'media')
            ->where('mshop_product_list.status', 1)
            ->orderBy('mshop_product_list.pos')
            ->first(['mshop_media.preview', 'mshop_media.link']);

        return $media->preview ?? $media->link ?? null;
    }

    private function normalizeTrackingNumber(?string $value): string
    {
        $value = preg_replace('/\s+/', '', trim((string) $value));

        if ($value === '') {
            return '';
        }

        return preg_match('/^(?=.*\d)[A-Z0-9][A-Z0-9._-]{4,39}$/i', $value) ? strtoupper($value) : '';
    }

    private function parseComplaintComment(?string $comment): array
    {
        $comment = (string) $comment;
        $proofPhotoUrl = null;
        $unboxingVideoUrl = null;
        $requestedResolution = null;
        $requestedRefundPercent = null;

        if (preg_match('/\[Requested Resolution:\s*([^\]]+)\]/', $comment, $matches)) {
            $requestedResolution = trim($matches[1]);
            $comment = trim(preg_replace('/\[Requested Resolution:\s*[^\]]+\]/', '', $comment));
        }

        if (preg_match('/\[Requested Refund Percent:\s*([^\]]+)\]/', $comment, $matches)) {
            $requestedRefundPercent = (int) trim($matches[1]);
            $comment = trim(preg_replace('/\[Requested Refund Percent:\s*[^\]]+\]/', '', $comment));
        }

        if (preg_match('/\[Proof Photo:\s*([^\]]+)\]/', $comment, $matches)) {
            $proofPhotoUrl = trim($matches[1]);
            $comment = trim(preg_replace('/\[Proof Photo:\s*[^\]]+\]/', '', $comment));
        }

        if (preg_match('/\[Unboxing Video:\s*([^\]]+)\]/', $comment, $matches)) {
            $unboxingVideoUrl = trim($matches[1]);
            $comment = trim(preg_replace('/\[Unboxing Video:\s*[^\]]+\]/', '', $comment));
        }

        if (preg_match('/\[Proof of Complaint:\s*([^\]]+)\]/', $comment, $matches)) {
            $proofPhotoUrl = $proofPhotoUrl ?: trim($matches[1]);
            $comment = trim(preg_replace('/\[Proof of Complaint:\s*[^\]]+\]/', '', $comment));
        }

        return [
            'text' => $comment,
            'proof_photo_url' => $proofPhotoUrl,
            'proof_url' => $proofPhotoUrl,
            'unboxing_video_url' => $unboxingVideoUrl,
            'requested_resolution' => $requestedResolution,
            'requested_resolution_label' => $this->getResolutionLabel($requestedResolution, $requestedRefundPercent),
            'requested_refund_percent' => $requestedRefundPercent,
        ];
    }

    private function getResolutionLabel(?string $resolution, ?int $refundPercent = null): string
    {
        return match ($resolution) {
            'return_refund' => 'Return + Refund',
            'full_refund' => 'Refund Penuh',
            'partial_refund' => 'Refund Sebagian' . ($refundPercent ? ' ' . $refundPercent . '%' : ''),
            default => '-',
        };
    }

    private function getOrderComplaints(string $orderId): array
    {
        return \DB::table('mshop_review')
            ->where('domain', 'order')
            ->where('refid', $orderId)
            ->orderByDesc('ctime')
            ->get(['id', 'status', 'name', 'customerid', 'comment', 'response', 'ctime', 'mtime'])
            ->map(function ($complaint) {
                $parsed = $this->parseComplaintComment($complaint->comment);

                return [
                    'id' => $complaint->id,
                    'status' => (int) $complaint->status,
                    'status_label' => (int) $complaint->status > 0 ? 'Menunggu Diproses' : 'Selesai',
                    'customer_name' => $complaint->name,
                    'customer_id' => $complaint->customerid,
                    'complaint' => $parsed['text'],
                    'proof_url' => $parsed['proof_url'],
                    'proof_photo_url' => $parsed['proof_photo_url'],
                    'unboxing_video_url' => $parsed['unboxing_video_url'],
                    'requested_resolution' => $parsed['requested_resolution'],
                    'requested_resolution_label' => $parsed['requested_resolution_label'],
                    'requested_refund_percent' => $parsed['requested_refund_percent'],
                    'response' => trim((string) $complaint->response),
                    'created_at' => $complaint->ctime,
                    'updated_at' => $complaint->mtime,
                ];
            })
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        try {
            MShop::cache(false); 
            MShop::cache(true);
            $orderIds = $this->getSellerOrderIds();
            $orders = \DB::table('mshop_order')
                ->whereIn('id', $orderIds)
                ->orderByDesc('ctime')
                ->get();
            
            $result = [];
            foreach ($orders as $order) {
                $grossAmount = $this->getSellerOrderProductTotal((string) $order->id);
                $commissionRate = $this->getCommissionRate();
                $platformFee = ($grossAmount * $commissionRate) / 100;
                $complaintCount = \DB::table('mshop_review')
                    ->where('domain', 'order')
                    ->where('refid', (string) $order->id)
                    ->count();

                $result[] = [
                    'id' => $order->id,
                    'date' => $order->ctime,
                    'status_payment' => (int) $order->statuspayment,
                    'status_delivery' => (int) $order->statusdelivery,
                    'price' => $grossAmount,
                    'price_total' => $grossAmount,
                    'commission_rate' => $commissionRate,
                    'platform_commission_fee' => $platformFee,
                    'seller_share' => $this->getSellerShare($grossAmount),
                    'currency' => $order->currencyid,
                    'tracking_number' => $this->normalizeTrackingNumber($order->comment),
                    'complaint_count' => $complaintCount,
                    'has_complaint' => $complaintCount > 0,
                ];
            }
            
            return response()->json([
                'message' => 'Daftar pesanan berhasil diambil.',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('SellerOrder index error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil daftar pesanan.'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $context = $this->getContextForOrder((string) $id);
            MShop::cache(false);
            MShop::cache(true);
            
            $orderManager = MShop::create($context, 'order');
            $order = $orderManager->get($id, ['order/product', 'order/address', 'order/service']);

            if (!$this->sellerOwnsOrder((string) $order->getId())) {
                return response()->json(['message' => 'Pesanan tidak ditemukan untuk toko ini.'], 404);
            }
            
            $grossAmount = $this->getSellerOrderProductTotal($order->getId());
            $commissionRate = $this->getCommissionRate();
            $platformFee = ($grossAmount * $commissionRate) / 100;

            $data = [
                'id' => $order->getId(),
                'date' => $order->getTimeCreated(),
                'status_payment' => $order->getStatusPayment(),
                'status_delivery' => $order->getStatusDelivery(),
                'price' => $grossAmount,
                'price_total' => $grossAmount + (float) $order->getPrice()->getCosts(),
                'commission_rate' => $commissionRate,
                'platform_commission_fee' => $platformFee,
                'seller_share' => $this->getSellerShare($grossAmount),
                'currency' => $order->getPrice()->getCurrencyId(),
                'tracking_number' => $this->normalizeTrackingNumber($order->getComment()),
                'complaints' => $this->getOrderComplaints((string) $order->getId()),
                'products' => [],
                'addresses' => [],
                'services' => []
            ];
            
            foreach ($order->getProducts() as $product) {
                if (!$this->productBelongsToSeller($product)) {
                    continue;
                }

                $data['products'][] = [
                    'id' => $product->getId(),
                    'product_id' => $product->getProductId(),
                    'name' => $product->getName(),
                    'code' => $product->getProductCode(),
                    'price' => $product->getPrice()->getValue(),
                    'quantity' => $product->getQuantity(),
                    'image' => $this->getProductImage($product->getProductId()),
                ];
            }
            
            foreach ($order->getAddresses() as $type => $addresses) {
                foreach ($addresses as $address) {
                    $data['addresses'][$type] = [
                        'firstname' => $address->getFirstname(),
                        'lastname' => $address->getLastname(),
                        'address1' => $address->getAddress1(),
                        'city' => $address->getCity(),
                        'telephone' => $address->getTelephone()
                    ];
                }
            }
            
            foreach ($order->getServices() as $type => $services) {
                foreach ($services as $service) {
                    $data['services'][$type] = [
                        'code' => $service->getCode(),
                        'name' => $service->getName(),
                        'price' => $service->getPrice()->getValue()
                    ];
                }
            }
            
            return response()->json([
                'message' => 'Detail pesanan berhasil diambil.',
                'data' => $data
            ]);
            
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            Log::error('SellerOrder show error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil detail pesanan.'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accept,reject',
            'tracking_number' => 'nullable|string'
        ]);

        try {
            $context = $this->getContextForOrder((string) $id);
            $orderManager = MShop::create($context, 'order');
            
            $orderManager->begin();
            $order = $orderManager->get($id);

            if (!$this->sellerOwnsOrder((string) $order->getId())) {
                $orderManager->rollback();
                return response()->json(['message' => 'Pesanan tidak ditemukan untuk toko ini.'], 404);
            }
            
            if ($request->status === 'accept') {
                $order->setStatusDelivery(Base::STAT_PROGRESS); // Diproses
            } else {
                $order->setStatusDelivery(Base::STAT_REFUSED); // Ditolak
            }

            if ($request->has('tracking_number')) {
                $order->setComment($request->tracking_number);
                $order->setStatusDelivery(Base::STAT_DISPATCHED); // Dikirim jika sudah ada resi
            }
            
            $orderManager->save($order);
            $orderManager->commit();
            
            // Send notification to customer
            try {
                $customerId = $order->getCustomerId();
                $customer = \App\Models\User::find($customerId);
                if ($customer) {
                    $statusType = 'accepted';
                    if ($request->status !== 'accept') {
                        $statusType = 'rejected';
                    } elseif ($request->filled('tracking_number')) {
                        $statusType = 'shipped';
                    }
                    $customer->notify(new OrderStatusUpdated($order->getId(), $statusType, $request->tracking_number));
                }
            } catch (\Exception $ex) {
                Log::error('Failed to send OrderStatusUpdated notification in updateStatus: ' . $ex->getMessage());
            }
            
            return response()->json([
                'message' => 'Status pesanan berhasil diperbarui.',
                'data' => [
                    'id' => $order->getId(),
                    'status_delivery' => $order->getStatusDelivery(),
                    'tracking_number' => $this->normalizeTrackingNumber($order->getComment()),
                ]
            ]);
            
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui pesanan: ' . $e->getMessage()], 500);
        }
    }

    public function respondComplaint(Request $request, $id)
    {
        $request->validate([
            'response_type' => 'required|in:accept,reject,partial_refund',
            'refund_percent' => 'nullable|required_if:response_type,partial_refund|integer|min:1|max:99',
            'message' => 'required|string|max:1000',
        ]);

        if (!$this->sellerOwnsOrder((string) $id)) {
            return response()->json(['message' => 'Pesanan tidak ditemukan untuk toko ini.'], 404);
        }

        $context = $this->getContextForOrder((string) $id);
        $reviewManager = MShop::create($context, 'review');

        try {
            $filter = $reviewManager->filter();
            $filter->add($filter->and([
                $filter->compare('==', 'review.domain', 'order'),
                $filter->compare('==', 'review.refid', (string) $id),
                $filter->compare('>', 'review.status', 0),
            ]));

            $items = $reviewManager->search($filter);
            if (count($items) === 0) {
                return response()->json(['message' => 'Komplain aktif tidak ditemukan.'], 404);
            }

            $complaintItem = $items->first();
            $type = $request->input('response_type');
            $label = match ($type) {
                'accept' => 'Seller setuju dengan solusi pembeli',
                'reject' => 'Seller menolak komplain',
                'partial_refund' => 'Seller menawarkan refund sebagian ' . (int) $request->input('refund_percent') . '%',
            };

            $response = "[SELLER RESPONSE: {$type}";
            if ($type === 'partial_refund') {
                $response .= ' ' . (int) $request->input('refund_percent') . '%';
            }
            $response .= " - {$label}]\n" . $request->input('message');

            $complaintItem->setResponse(trim($complaintItem->getResponse() . "\n\n" . $response));
            $reviewManager->save($complaintItem);

            return response()->json(['message' => 'Tanggapan komplain berhasil dikirim.']);
        } catch (\Exception $e) {
            Log::error('Seller complaint response error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim tanggapan komplain.'], 500);
        }
    }

    public function requestPickup(Request $request, $id)
    {
        try {
            $context = $this->getContextForOrder((string) $id);
            $orderManager = MShop::create($context, 'order');
            
            $orderManager->begin();
            $order = $orderManager->get($id);

            if (!$this->sellerOwnsOrder((string) $order->getId())) {
                $orderManager->rollback();
                return response()->json(['message' => 'Pesanan tidak ditemukan untuk toko ini.'], 404);
            }
            
            // Mock third-party logistics call here
            
            // Set status to Dispatched/Dikirim
            $order->setStatusDelivery(Base::STAT_DISPATCHED);
            
            $orderManager->save($order);
            $orderManager->commit();

            // Send notification to customer
            try {
                $customerId = $order->getCustomerId();
                $customer = \App\Models\User::find($customerId);
                if ($customer) {
                    $customer->notify(new OrderStatusUpdated($order->getId(), 'shipped'));
                }
            } catch (\Exception $ex) {
                Log::error('Failed to send OrderStatusUpdated notification in requestPickup: ' . $ex->getMessage());
            }
            
            return response()->json([
                'message' => 'Permintaan pickup berhasil. Kurir akan segera menjemput paket.',
                'data' => [
                    'id' => $order->getId(),
                    'status_delivery' => $order->getStatusDelivery(),
                ]
            ]);
            
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pickup: ' . $e->getMessage()], 500);
        }
    }
}

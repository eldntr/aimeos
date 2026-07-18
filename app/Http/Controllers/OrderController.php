<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;

class OrderController extends Controller
{
    protected function getContext()
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

    private function getProductShopMeta($product): array
    {
        $vendor = method_exists($product, 'getVendor') ? trim($product->getVendor()) : '';
        $productId = $product->getProductId();

        $site = null;
        if ($productId) {
            $site = \DB::table('mshop_product')
                ->join('mshop_locale_site', 'mshop_product.siteid', '=', 'mshop_locale_site.siteid')
                ->where('mshop_product.id', $productId)
                ->first([
                    'mshop_locale_site.label as label',
                    'mshop_locale_site.code as code',
                ]);
        }

        return [
            'shop_name' => $vendor ?: ($site->label ?? 'Toko Reborns'),
            'shop_code' => $site->code ?? null,
        ];
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

    private function getReviewedProductIds(array $productIds): array
    {
        $productIds = array_values(array_filter(array_unique(array_map('strval', $productIds))));

        if ($productIds === []) {
            return [];
        }

        return \DB::table('mshop_review')
            ->where('domain', 'product')
            ->where('customerid', (string) auth()->id())
            ->whereIn('refid', $productIds)
            ->where('status', '>', 0)
            ->pluck('refid')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();
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
            'proof_url' => $proofPhotoUrl,
            'proof_photo_url' => $proofPhotoUrl,
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
            ->where('customerid', (string) auth()->id())
            ->orderByDesc('ctime')
            ->get(['id', 'status', 'comment', 'response', 'ctime', 'mtime'])
            ->map(function ($complaint) {
                $parsed = $this->parseComplaintComment($complaint->comment);

                return [
                    'id' => $complaint->id,
                    'status' => (int) $complaint->status,
                    'status_label' => (int) $complaint->status > 0 ? 'Menunggu Diproses' : 'Selesai',
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
            $context = $this->getContext();
            // Mencegah cache state antar request di test
            MShop::cache(false); 
            MShop::cache(true);
            
            $orderManager = MShop::create($context, 'order');
            
            $search = $orderManager->filter();
            
            // Filter berdasarkan customer ID yang sedang login
            $search->add($search->compare('==', 'order.customerid', (string) auth()->id()));
            
            // Mengecualikan pesanan yang masih dalam keranjang (PAY_UNFINISHED = -1)
            $search->add($search->compare('>', 'order.statuspayment', Base::PAY_UNFINISHED));
            
            // Urutkan dari yang terbaru
            $search->slice(0, 50)->order('-order.ctime');
            
            $orders = $orderManager->search($search, ['order/product', 'order/service']);
            
            $result = [];
            foreach ($orders as $order) {
                $products = [];
                $productIds = [];
                foreach ($order->getProducts() as $product) {
                    $productIds[] = $product->getProductId();
                }
                $reviewedProductIds = $this->getReviewedProductIds($productIds);

                foreach ($order->getProducts() as $product) {
                    $shop = $this->getProductShopMeta($product);
                    $isReviewed = in_array((string) $product->getProductId(), $reviewedProductIds, true);
                    $products[] = [
                        'id' => $product->getId(),
                        'product_id' => $product->getProductId(),
                        'name' => $product->getName(),
                        'code' => $product->getProductCode(),
                        'price' => (float) $product->getPrice()->getValue(),
                        'quantity' => $product->getQuantity(),
                        'shop_name' => $shop['shop_name'],
                        'shop_code' => $shop['shop_code'],
                        'image' => $this->getProductImage($product->getProductId()),
                        'reviewed' => $isReviewed,
                    ];
                }
                $hasProducts = count($products) > 0;
                $reviewedCount = count(array_filter($products, fn ($product) => $product['reviewed']));
                $complaintCount = \DB::table('mshop_review')
                    ->where('domain', 'order')
                    ->where('refid', (string) $order->getId())
                    ->where('customerid', (string) auth()->id())
                    ->count();

                $createdAt = $order->getTimeCreated();
                $result[] = [
                    'id' => $order->getId(),
                    'code' => 'ORD-' . $order->getId(),
                    'date' => $createdAt,
                    'created_at' => $createdAt,
                    'status_payment' => $order->getStatusPayment(),
                    'status_delivery' => $order->getStatusDelivery(),
                    'price' => (float) $order->getPrice()->getValue(),
                    'price_total' => (float) $order->getPrice()->getValue() + (float) $order->getPrice()->getCosts(),
                    'currency' => $order->getPrice()->getCurrencyId(),
                    'reviewed' => $hasProducts && $reviewedCount === count($products),
                    'reviewed_count' => $reviewedCount,
                    'reviewable_count' => count($products),
                    'complaint_count' => $complaintCount,
                    'has_complaint' => $complaintCount > 0,
                    'products' => $products,
                ];
            }
            
            return response()->json([
                'message' => 'Riwayat pesanan berhasil diambil.',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('OrderHistory index error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil riwayat pesanan.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $context = $this->getContext();
            MShop::cache(false);
            MShop::cache(true);
            
            $orderManager = MShop::create($context, 'order');
            
            // Mengambil pesanan beserta relasinya
            $order = $orderManager->get($id, ['order/product', 'order/address', 'order/service']);
            
            // Pastikan pesanan milik user yang sedang login
            if ($order->getCustomerId() !== (string) auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Jika status masih pending, kita buatkan mock payment url agar user bisa membayar
            $paymentUrl = null;
            if ($order->getStatusPayment() === Base::PAY_PENDING) {
                $paymentUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-token-from-history-" . $order->getId();
            }
            
            $trackingNumber = $this->normalizeTrackingNumber($order->getComment());

            $data = [
                'id' => $order->getId(),
                'code' => 'ORD-' . $order->getId(),
                'date' => $order->getTimeCreated(),
                'created_at' => $order->getTimeCreated(),
                'status_payment' => $order->getStatusPayment(),
                'status_delivery' => $order->getStatusDelivery(),
                'price' => (float) $order->getPrice()->getValue(),
                'price_total' => (float) $order->getPrice()->getValue() + (float) $order->getPrice()->getCosts(),
                'currency' => $order->getPrice()->getCurrencyId(),
                'payment_url' => $paymentUrl,
                'tracking_number' => $trackingNumber,
                'tracking_status' => $trackingNumber ? 'Nomor resi sudah tersedia' : 'Menunggu nomor resi dari penjual',
                'tracking_history' => [],
                'products' => [],
                'addresses' => [],
                'services' => []
            ];
            
            $productIds = [];
            foreach ($order->getProducts() as $product) {
                $productIds[] = $product->getProductId();
            }
            $reviewedProductIds = $this->getReviewedProductIds($productIds);

            foreach ($order->getProducts() as $product) {
                $shop = $this->getProductShopMeta($product);
                $isReviewed = in_array((string) $product->getProductId(), $reviewedProductIds, true);
                $data['products'][] = [
                    'id' => $product->getId(),
                    'product_id' => $product->getProductId(),
                    'name' => $product->getName(),
                    'code' => $product->getProductCode(),
                    'price' => $product->getPrice()->getValue(),
                    'quantity' => $product->getQuantity(),
                    'shop_name' => $shop['shop_name'],
                    'shop_code' => $shop['shop_code'],
                    'image' => $this->getProductImage($product->getProductId()),
                    'reviewed' => $isReviewed,
                ];
            }
            $data['reviewed_count'] = count(array_filter($data['products'], fn ($product) => $product['reviewed']));
            $data['reviewable_count'] = count($data['products']);
            $data['reviewed'] = count($data['products']) > 0 && $data['reviewed_count'] === count($data['products']);
            $data['complaints'] = $this->getOrderComplaints((string) $order->getId());
            
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
            Log::error('OrderHistory show error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil detail pesanan.'], 500);
        }
    }

    public function markReceived($id)
    {
        $order = \DB::table('mshop_order')
            ->where('id', $id)
            ->where('customerid', (string) auth()->id())
            ->first(['id', 'statusdelivery']);

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ((int) $order->statusdelivery !== Base::STAT_DISPATCHED) {
            return response()->json(['message' => 'Pesanan belum dalam status dikirim.'], 422);
        }

        \DB::table('mshop_order')
            ->where('id', $id)
            ->update([
                'statusdelivery' => Base::STAT_DELIVERED,
                'datedelivery' => now(),
                'mtime' => now(),
            ]);

        return response()->json([
            'message' => 'Pesanan ditandai sudah diterima.',
            'data' => [
                'id' => (string) $id,
                'status_delivery' => Base::STAT_DELIVERED,
            ],
        ]);
    }
}

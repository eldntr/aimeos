<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;

class OrderController extends Controller
{
    protected function getNumericSiteId($siteid)
    {
        $parts = array_filter(explode('.', trim($siteid, '.')));
        return end($parts);
    }

    protected function getSellerContext()
    {
        $context = app('aimeos.context')->get(false);
        $user = auth()->user();
        if ($user && $user->siteid) {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $numericId = $this->getNumericSiteId($user->siteid);
            $siteItem = $siteManager->get($numericId);
            $context->setLocale(app('aimeos.locale')->get($context, $siteItem->getCode()));
        }
        return $context;
    }

    public function index(Request $request)
    {
        try {
            $context = $this->getSellerContext();
            MShop::cache(false); 
            MShop::cache(true);
            
            $orderManager = MShop::create($context, 'order');
            $search = $orderManager->filter();
            
            // Only fetch orders that are checked out
            $search->add($search->compare('>', 'order.statuspayment', Base::PAY_UNFINISHED));
            $search->slice(0, 50)->order('-order.ctime');
            
            $orders = $orderManager->search($search);
            
            $result = [];
            foreach ($orders as $order) {
                $result[] = [
                    'id' => $order->getId(),
                    'date' => $order->getTimeCreated(),
                    'status_payment' => $order->getStatusPayment(),
                    'status_delivery' => $order->getStatusDelivery(),
                    'price' => $order->getPrice()->getValue(),
                    'currency' => $order->getPrice()->getCurrencyId(),
                    'tracking_number' => $order->getComment(), // We map comment to tracking
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
            $context = $this->getSellerContext();
            MShop::cache(false);
            MShop::cache(true);
            
            $orderManager = MShop::create($context, 'order');
            $order = $orderManager->get($id, ['order/product', 'order/address', 'order/service']);
            
            $data = [
                'id' => $order->getId(),
                'date' => $order->getTimeCreated(),
                'status_payment' => $order->getStatusPayment(),
                'status_delivery' => $order->getStatusDelivery(),
                'price' => $order->getPrice()->getValue(),
                'currency' => $order->getPrice()->getCurrencyId(),
                'tracking_number' => $order->getComment(),
                'products' => [],
                'addresses' => [],
                'services' => []
            ];
            
            foreach ($order->getProducts() as $product) {
                $data['products'][] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'code' => $product->getProductCode(),
                    'price' => $product->getPrice()->getValue(),
                    'quantity' => $product->getQuantity()
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
            $context = $this->getSellerContext();
            $orderManager = MShop::create($context, 'order');
            
            $orderManager->begin();
            $order = $orderManager->get($id);
            
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
            
            return response()->json([
                'message' => 'Status pesanan berhasil diperbarui.',
                'data' => [
                    'id' => $order->getId(),
                    'status_delivery' => $order->getStatusDelivery(),
                    'tracking_number' => $order->getComment(),
                ]
            ]);
            
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui pesanan: ' . $e->getMessage()], 500);
        }
    }

    public function requestPickup(Request $request, $id)
    {
        try {
            $context = $this->getSellerContext();
            $orderManager = MShop::create($context, 'order');
            
            $orderManager->begin();
            $order = $orderManager->get($id);
            
            // Mock third-party logistics call here
            
            // Set status to Dispatched/Dikirim
            $order->setStatusDelivery(Base::STAT_DISPATCHED);
            
            $orderManager->save($order);
            $orderManager->commit();
            
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

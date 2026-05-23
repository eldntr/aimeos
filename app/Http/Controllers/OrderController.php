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
            
            $data = [
                'id' => $order->getId(),
                'date' => $order->getTimeCreated(),
                'status_payment' => $order->getStatusPayment(),
                'status_delivery' => $order->getStatusDelivery(),
                'price' => $order->getPrice()->getValue(),
                'currency' => $order->getPrice()->getCurrencyId(),
                'payment_url' => $paymentUrl,
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
            Log::error('OrderHistory show error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil detail pesanan.'], 500);
        }
    }
}

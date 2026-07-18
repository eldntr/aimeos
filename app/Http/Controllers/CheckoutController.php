<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Aimeos\MShop\Order\Item\Base as OrderBase;

class CheckoutController extends Controller
{
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
     * Get the current pending order for authenticated user from DB.
     */
    private function getOrderForUser()
    {
        $context = $this->getContextWithLocale();
        $user = auth()->user();
        
        \Aimeos\MShop::cache(false);
        \Aimeos\MShop::cache(true);
        
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $filter = $orderManager->filter();
        $filter->add($filter->and([
            $filter->compare('==', 'order.customerid', $user->id),
            $filter->compare('==', 'order.statuspayment', OrderBase::PAY_UNFINISHED),
            $filter->compare('==', 'order.statusdelivery', OrderBase::STAT_UNFINISHED),
        ]));
        
        return $orderManager->search($filter, ['order/product', 'order/address', 'order/service'])->first();
    }

    private function getBasketController()
    {
        $context = $this->getContextWithLocale();
        $user = auth()->user();
        
        \Aimeos\MShop::cache(false);
        \Aimeos\MShop::cache(true);
        
        $context = $this->getContextWithLocale();
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $filter = $orderManager->filter();
        $filter->add($filter->and([
            $filter->compare('==', 'order.customerid', $user->id),
            $filter->compare('==', 'order.statuspayment', OrderBase::PAY_UNFINISHED),
            $filter->compare('==', 'order.statusdelivery', OrderBase::STAT_UNFINISHED),
        ]));
        
        $order = $orderManager->search($filter, ['order/product', 'order/address', 'order/service'])->first();

        if (!$order) {
            $order = $orderManager->create();
            $order->setCustomerId($user->id);
            $order->setStatusPayment(OrderBase::PAY_UNFINISHED);
            $order->setStatusDelivery(OrderBase::STAT_UNFINISHED);
        }

        $orderLocale = $order->locale();
        $ref = new \ReflectionClass($orderLocale);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($orderLocale, $context->locale()->getSiteItem());
        }

        $orderManager->setSession($order, 'default');
        $context->config()->set('controller/frontend/basket/decorators/local', []);
        
        return \Aimeos\Controller\Frontend::create($context, 'basket');
    }

    private function saveBasket($basketController)
    {
        $context = $this->getContextWithLocale();
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $order = $basketController->get();
        
        if (auth()->check()) {
            $order->setCustomerId((string) auth()->user()->id);
        }

        $orderManager->begin();
        try {
            $orderManager->save($order);
            
            // Save addresses
            $orderAddressManager = \Aimeos\MShop::create($context, 'order/address');
            foreach ($order->getAddresses() as $type => $addressItems) {
                foreach ($addressItems as $addressItem) {
                    $addressItem->setParentId($order->getId());
                    $orderAddressManager->save($addressItem);
                }
            }

            // Save services (shipping/payment)
            $orderServiceManager = \Aimeos\MShop::create($context, 'order/service');
            foreach ($order->getServices() as $type => $serviceItems) {
                foreach ($serviceItems as $serviceItem) {
                    $serviceItem->setParentId($order->getId());
                    $orderServiceManager->save($serviceItem);
                }
            }

            $orderManager->commit();
        } catch (\Exception $e) {
            $orderManager->rollback();
            throw $e;
        }
    }

    /**
     * Set delivery address for checkout using Address ID from user's address book.
     */
    public function saveAddress(Request $request)
    {
        $request->validate([
            'address_id' => 'required|string',
        ]);

        $order = $this->getOrderForUser();
        if (!$order || count($order->getProducts()) === 0) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }

        $context = $this->getContextWithLocale();

        // Load the selected address from customer's address book
        $customerAddressManager = \Aimeos\MShop::create($context, 'customer/address');
        try {
            $customerAddress = $customerAddressManager->get($request->address_id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Alamat tidak ditemukan.'], 404);
        }

        // Verify ownership
        if ($customerAddress->getParentId() != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Directly upsert delivery and billing addresses into order/address
        $this->upsertOrderAddress($context, $order->getId(), 'delivery', $customerAddress);
        $this->upsertOrderAddress($context, $order->getId(), 'payment', $customerAddress);

        return response()->json([
            'message' => 'Alamat pengiriman berhasil dipilih.',
            'data'    => ['address_id' => $request->address_id]
        ]);
    }

    /**
     * Upsert an address into mshop_order_address.
     */
    private function upsertOrderAddress($context, $orderId, string $type, $customerAddress)
    {
        $addressManager = \Aimeos\MShop::create($context, 'order/address');

        // find existing address of same type
        $filter = $addressManager->filter(true)->add([
            'order.address.parentid' => $orderId,
            'order.address.type'     => $type,
        ]);
        $existing = $addressManager->search($filter)->first();

        if ($existing) {
            $addrItem = $existing;
        } else {
            $addrItem = $addressManager->create();
            $addrItem->setParentId($orderId);
            $addrItem->setType($type);
            $addrItem->setPosition(0);
        }

        $addrItem->setFirstname($customerAddress->getFirstname());
        $addrItem->setLastname($customerAddress->getLastname());
        $addrItem->setAddress1($customerAddress->getAddress1());
        $addrItem->setAddress2($customerAddress->getAddress2());
        $addrItem->setCity($customerAddress->getCity());
        $addrItem->setPostal($customerAddress->getPostal());
        $addrItem->setTelephone($customerAddress->getTelephone());
        $addrItem->setEmail($customerAddress->getEmail());
        $addrItem->setCountryId($customerAddress->getCountryId() ?: 'ID');
        $addrItem->setLanguageId($customerAddress->getLanguageId() ?: 'id');

        $addressManager->save($addrItem);
    }

    /**
     * Get shipping options using RajaOngkir calculator
     */
    public function getShippingOptions()
    {
        $order = $this->getOrderForUser();
        if (!$order) {
            return response()->json(['data' => []]);
        }

        $context = $this->getContextWithLocale();
        $addressManager = \Aimeos\MShop::create($context, 'order/address');
        $filter = $addressManager->filter(true)->add([
            'order.address.parentid' => $order->getId(),
            'order.address.type'     => 'delivery',
        ]);
        $deliveryAddress = $addressManager->search($filter)->first();

        // Map Indonesian cities to simulated RajaOngkir city IDs
        $cityId = '152'; // Default: Jakarta Pusat
        if ($deliveryAddress) {
            $cityName = strtolower($deliveryAddress->getCity());
            if (str_contains($cityName, 'barat')) $cityId = '151';
            elseif (str_contains($cityName, 'bandung')) $cityId = '23';
            elseif (str_contains($cityName, 'surabaya')) $cityId = '444';
            elseif (str_contains($cityName, 'yogyakarta') || str_contains($cityName, 'jogja')) $cityId = '501';
            elseif (str_contains($cityName, 'medan')) $cityId = '256';
            elseif (str_contains($cityName, 'semarang')) $cityId = '399';
            elseif (str_contains($cityName, 'depok')) $cityId = '115';
            elseif (str_contains($cityName, 'bekasi')) $cityId = '55';
        }

        // Calculate total weight (default 1kg per product)
        $weightGrams = 0;
        foreach ($order->getProducts() as $product) {
            $weightGrams += ($product->getQuantity() * 1000);
        }
        $weightGrams = max(1000, $weightGrams);

        $rajaOngkir = new \App\Services\RajaOngkirService();
        
        $couriers = ['jne', 'jnt', 'sicepat'];
        $options = [];

        foreach ($couriers as $courier) {
            $res = $rajaOngkir->calculateCost($cityId, $weightGrams, $courier);
            if (!empty($res) && isset($res['costs'][0]['cost'][0]['value'])) {
                $costVal = $res['costs'][0]['cost'][0]['value'];
                $serviceName = $res['name'] . ' (' . ($res['costs'][0]['service'] ?? 'REG') . ')';
                $code = $courier . '_' . strtolower($res['costs'][0]['service'] ?? 'reg');
                
                $options[] = [
                    'code' => $code,
                    'name' => $serviceName,
                    'price' => (float) $costVal
                ];
            }
        }

        // If no options are calculated, return fallback
        if (empty($options)) {
            $options = [
                ['code' => 'jne_reg', 'name' => 'JNE Reguler (REG)', 'price' => 15000],
                ['code' => 'jnt_reg', 'name' => 'J&T Express (REG)', 'price' => 12000],
                ['code' => 'sicepat_reg', 'name' => 'SiCepat Ekspres (REG)', 'price' => 10000],
            ];
        }

        return response()->json([
            'data' => $options
        ]);
    }

    /**
     * Select a shipping method.
     */
    public function saveShipping(Request $request)
    {
        $request->validate([
            'shipping_code' => 'required|string',
        ]);

        $order = $this->getOrderForUser();
        if (!$order || count($order->getProducts()) === 0) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }

        // Extract courier and service from code (e.g. jne_reg)
        $parts = explode('_', $request->shipping_code);
        $courier = $parts[0] ?? 'jne';
        
        $context = $this->getContextWithLocale();
        $addressManager = \Aimeos\MShop::create($context, 'order/address');
        $filter = $addressManager->filter(true)->add([
            'order.address.parentid' => $order->getId(),
            'order.address.type'     => 'delivery',
        ]);
        $deliveryAddress = $addressManager->search($filter)->first();

        $cityId = '152'; // Default
        if ($deliveryAddress) {
            $cityName = strtolower($deliveryAddress->getCity());
            if (str_contains($cityName, 'barat')) $cityId = '151';
            elseif (str_contains($cityName, 'bandung')) $cityId = '23';
            elseif (str_contains($cityName, 'surabaya')) $cityId = '444';
            elseif (str_contains($cityName, 'yogyakarta') || str_contains($cityName, 'jogja')) $cityId = '501';
            elseif (str_contains($cityName, 'medan')) $cityId = '256';
            elseif (str_contains($cityName, 'semarang')) $cityId = '399';
            elseif (str_contains($cityName, 'depok')) $cityId = '115';
            elseif (str_contains($cityName, 'bekasi')) $cityId = '55';
        }

        $weightGrams = 0;
        foreach ($order->getProducts() as $product) {
            $weightGrams += ($product->getQuantity() * 1000);
        }
        $weightGrams = max(1000, $weightGrams);

        $rajaOngkir = new \App\Services\RajaOngkirService();
        $res = $rajaOngkir->calculateCost($cityId, $weightGrams, $courier);

        if (!empty($res) && isset($res['costs'][0]['cost'][0]['value'])) {
            $price = (float) $res['costs'][0]['cost'][0]['value'];
            $name = $res['name'] . ' (' . ($res['costs'][0]['service'] ?? 'REG') . ')';
        } else {
            // Fallback
            $price = 15000;
            $name = 'JNE Reguler (REG)';
        }

        $context = $this->getContextWithLocale();
        $this->upsertOrderService($context, $order->getId(), 'delivery', $request->shipping_code, $name, $price);

        return response()->json([
            'message' => 'Layanan pengiriman berhasil dipilih.',
            'data'    => ['shipping_code' => $request->shipping_code, 'shipping_name' => $name, 'price' => $price]
        ]);
    }

    /**
     * Get dummy payment options.
     */
    public function getPaymentOptions()
    {
        return response()->json([
            'data' => [
                ['code' => 'bank_transfer', 'name' => 'Bank Transfer'],
                ['code' => 'gopay', 'name' => 'GoPay'],
                ['code' => 'credit_card', 'name' => 'Credit Card'],
            ]
        ]);
    }

    /**
     * Select a payment method.
     */
    public function savePayment(Request $request)
    {
        $request->validate([
            'payment_code' => 'required|string',
        ]);

        $order = $this->getOrderForUser();
        if (!$order || count($order->getProducts()) === 0) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }

        $options = [
            'bank_transfer' => 'Bank Transfer',
            'gopay'         => 'GoPay',
            'credit_card'   => 'Credit Card',
        ];

        if (!array_key_exists($request->payment_code, $options)) {
            return response()->json(['message' => 'Metode pembayaran tidak valid.'], 400);
        }

        $context = $this->getContextWithLocale();

        // Directly upsert into order/service table
        $this->upsertOrderService($context, $order->getId(), 'payment', $request->payment_code, $options[$request->payment_code], null);

        return response()->json([
            'message' => 'Metode pembayaran berhasil dipilih.',
            'data'    => ['payment_code' => $request->payment_code, 'payment_name' => $options[$request->payment_code]]
        ]);
    }

    /**
     * Upsert a service record directly in mshop_order_service.
     */
    private function upsertOrderService($context, $orderId, string $type, string $code, string $name, $price)
    {
        $serviceManager = \Aimeos\MShop::create($context, 'order/service');

        // Check existing service of same type for this order
        $filter = $serviceManager->filter(true)->add([
            'order.service.parentid' => $orderId,
            'order.service.type'     => $type,
        ]);
        $existing = $serviceManager->search($filter)->first();

        if ($existing) {
            $serviceItem = $existing;
        } else {
            $serviceItem = $serviceManager->create();
            $serviceItem->setParentId($orderId);
            $serviceItem->setType($type);
            $serviceItem->setPosition(1);
        }

        // Create & save new service item
        $priceItem = \Aimeos\MShop::create($context, 'price')->create()
            ->setCurrencyId('IDR')
            ->setValue($price !== null ? (string)$price : '0.00');

        $serviceItem->setCode($code)
            ->setName($name)
            ->setPrice($priceItem);

        $serviceManager->save($serviceItem);
    }

    /**
     * Finalize checkout.
     */
    public function processOrder(Request $request)
    {
        $order = $this->getOrderForUser();
        if (!$order || count($order->getProducts()) === 0) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }

        // Resolve context first (needed for all DB checks below)
        $context = $this->getContextWithLocale();

        if (count($order->getAddresses('delivery')) === 0) {
            // Fallback: read addresses directly from DB
            $addressManager = \Aimeos\MShop::create($context, 'order/address');
            $aFilter = $addressManager->filter(true)->add([
                'order.address.parentid' => $order->getId(),
                'order.address.type'     => 'delivery',
            ]);
            $addresses = $addressManager->search($aFilter);
            if (count($addresses) === 0) {
                return response()->json(['message' => 'Alamat pengiriman belum dipilih.'], 400);
            }
        }

        // Check services directly from DB
        $serviceManager = \Aimeos\MShop::create($context, 'order/service');
        $sFilter = $serviceManager->filter(true)->add(['order.service.parentid' => $order->getId()]);
        $services = $serviceManager->search($sFilter);

        $hasDelivery = false;
        $hasPayment  = false;
        foreach ($services as $srv) {
            if ($srv->getType() === 'delivery') $hasDelivery = true;
            if ($srv->getType() === 'payment')  $hasPayment  = true;
        }

        if (!$hasDelivery) {
            return response()->json(['message' => 'Layanan pengiriman belum dipilih.'], 400);
        }

        if (!$hasPayment) {
            return response()->json(['message' => 'Metode pembayaran belum dipilih.'], 400);
        }

        $orderManager = \Aimeos\MShop::create($context, 'order');
        $order->setStatusPayment(OrderBase::PAY_PENDING);
        $order->setStatusDelivery(OrderBase::STAT_PENDING);
        $orderManager->save($order);

        // Mock Midtrans payment URL
        $mockPaymentUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-token-' . uniqid();

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => [
                'order_id'    => $order->getId(),
                'payment_url' => $mockPaymentUrl,
                'status'      => 'pending'
            ]
        ], 201);
    }
}

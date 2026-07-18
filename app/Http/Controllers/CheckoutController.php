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
        
        $orderId = \Illuminate\Support\Facades\DB::table('mshop_order')
            ->where('customerid', $user->id)
            ->where('statuspayment', OrderBase::PAY_UNFINISHED)
            ->where('statusdelivery', OrderBase::STAT_UNFINISHED)
            ->orderByDesc('id')
            ->value('id');

        return $orderId ? \Aimeos\MShop::create($context, 'order')->get($orderId, ['order/product']) : null;
    }

    private function getBasketController()
    {
        $context = $this->getContextWithLocale();
        $user = auth()->user();
        
        \Aimeos\MShop::cache(false);
        \Aimeos\MShop::cache(true);
        
        $context = $this->getContextWithLocale();
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $orderId = \Illuminate\Support\Facades\DB::table('mshop_order')
            ->where('customerid', $user->id)
            ->where('statuspayment', OrderBase::PAY_UNFINISHED)
            ->where('statusdelivery', OrderBase::STAT_UNFINISHED)
            ->orderByDesc('id')
            ->value('id');

        $order = $orderId ? $orderManager->get($orderId, ['order/product']) : null;

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

    private function selectedPositions(Request $request): array
    {
        $positions = $request->input('selected_positions', []);

        if (is_string($positions)) {
            $positions = array_filter(explode(',', $positions), fn ($value) => $value !== '');
        }

        if (!is_array($positions)) {
            return [];
        }

        return array_values(array_unique(array_map('strval', $positions)));
    }

    private function getSelectedProducts($order, Request $request): array
    {
        $selected = $this->selectedPositions($request);
        $products = [];

        foreach ($order->getProducts() as $pos => $product) {
            if (empty($selected) || in_array((string) $pos, $selected, true)) {
                $products[] = $product;
            }
        }

        return $products;
    }

    private function destinationIdFromAddress($deliveryAddress): string
    {
        if (!$deliveryAddress) {
            throw new \RuntimeException('Alamat tujuan belum dipilih.');
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('tb_ro_cities')) {
            $cityName = strtolower($deliveryAddress->getCity());
            if (str_contains($cityName, 'barat')) return '151';
            if (str_contains($cityName, 'bandung')) return '23';
            if (str_contains($cityName, 'surabaya')) return '444';
            if (str_contains($cityName, 'yogyakarta') || str_contains($cityName, 'jogja')) return '501';
            if (str_contains($cityName, 'medan')) return '256';
            if (str_contains($cityName, 'semarang')) return '399';
            if (str_contains($cityName, 'depok')) return '115';
            if (str_contains($cityName, 'bekasi')) return '55';
            return '152';
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('mshop_order_address', 'komerce_destination_id')) {
            $destinationId = \Illuminate\Support\Facades\DB::table('mshop_order_address')
                ->where('id', $deliveryAddress->getId())
                ->value('komerce_destination_id');

            if ($destinationId) {
                return (string) $destinationId;
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('mshop_order_address', 'ro_city_id')) {
            $cityId = \Illuminate\Support\Facades\DB::table('mshop_order_address')
                ->where('id', $deliveryAddress->getId())
                ->value('ro_city_id');

            if ($cityId) {
                return (string) $cityId;
            }
        }

        $cityName = trim((string) $deliveryAddress->getCity());
        $postal = trim((string) $deliveryAddress->getPostal());
        $city = \Illuminate\Support\Facades\DB::table('tb_ro_cities')
            ->when($postal !== '', fn ($query) => $query->orderByRaw('postal_code = ? desc', [$postal]))
            ->where('city_name', 'like', '%' . $cityName . '%')
            ->orWhereRaw('? like concat("%", city_name, "%")', [$cityName])
            ->orderBy('city_id')
            ->first(['city_id']);

        if (!$city && $cityName !== '') {
            $normalized = preg_replace('/^(kota|kabupaten)\s+/i', '', $cityName);
            $city = \Illuminate\Support\Facades\DB::table('tb_ro_cities')
                ->where('city_name', 'like', '%' . $normalized . '%')
                ->orderBy('city_id')
                ->first(['city_id']);
        }

        if (!$city) {
            throw new \RuntimeException('Alamat tujuan belum punya kota RajaOngkir. Pilih ulang alamat pengiriman dari daftar lokasi.');
        }

        return (string) $city->city_id;
    }

    private function getSellerShippingConfigByProductId(?string $productId): array
    {
        $site = \Illuminate\Support\Facades\DB::table('mshop_product')
            ->join('mshop_locale_site', 'mshop_product.siteid', '=', 'mshop_locale_site.siteid')
            ->where('mshop_product.id', $productId)
            ->first(['mshop_locale_site.siteid', 'mshop_locale_site.label', 'mshop_locale_site.config']);

        $config = [];
        if ($site && $site->config) {
            $decoded = json_decode($site->config, true);
            $config = is_array($decoded) ? $decoded : [];
        }

        return [
            'shop_name' => $site->label ?? 'Seller',
            'origin_id' => $config['shipping.komerce_destination_id'] ?? $config['shipping.origin_id'] ?? (
                \Illuminate\Support\Facades\Schema::hasTable('tb_ro_cities') ? null : '152'
            ),
            'couriers' => $this->sellerCourierCodes($site->siteid ?? null),
        ];
    }

    private function sellerCourierCodes(?string $siteid): array
    {
        if (!$siteid || !\Illuminate\Support\Facades\Schema::hasTable('seller_shipping_couriers')) {
            return $this->defaultCourierCodes();
        }

        $codes = \Illuminate\Support\Facades\DB::table('seller_shipping_couriers')
            ->join('shipping_couriers', 'seller_shipping_couriers.courier_code', '=', 'shipping_couriers.code')
            ->where('seller_shipping_couriers.siteid', $siteid)
            ->where('shipping_couriers.active', true)
            ->where('shipping_couriers.supports_domestic_cost', true)
            ->pluck('seller_shipping_couriers.courier_code')
            ->map(fn ($code) => (string) $code)
            ->all();

        return $codes ?: $this->defaultCourierCodes();
    }

    private function defaultCourierCodes(): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('shipping_couriers')) {
            return ['jne', 'jnt', 'sicepat'];
        }

        return \Illuminate\Support\Facades\DB::table('shipping_couriers')
            ->where('active', true)
            ->where('supports_domestic_cost', true)
            ->orderBy('name')
            ->pluck('code')
            ->map(fn ($code) => (string) $code)
            ->all();
    }

    private function productWeightGrams(?string $productId): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('mshop_product', 'weight_grams')) {
            return 1000;
        }

        return max(1, (int) (\Illuminate\Support\Facades\DB::table('mshop_product')->where('id', $productId)->value('weight_grams') ?: 1000));
    }

    private function groupedShipmentWeights(array $products): array
    {
        $groups = [];

        foreach ($products as $product) {
            $shipping = $this->getSellerShippingConfigByProductId($product->getProductId());
            if (empty($shipping['origin_id'])) {
                throw new \RuntimeException('Alamat asal pengiriman toko ' . $shipping['shop_name'] . ' belum lengkap. Seller perlu memilih kota asal di Profil Toko.');
            }

            $originId = (string) $shipping['origin_id'];
            $groups[$originId] ??= [
                'origin_id' => $originId,
                'shop_name' => $shipping['shop_name'],
                'couriers' => $shipping['couriers'],
                'weight_grams' => 0,
            ];
            $groups[$originId]['weight_grams'] += (int) $product->getQuantity() * $this->productWeightGrams($product->getProductId());
        }

        return array_values($groups);
    }

    private function calculateShippingOptions($order, Request $request, $deliveryAddress): array
    {
        $destinationId = $this->destinationIdFromAddress($deliveryAddress);
        $shipmentGroups = $this->groupedShipmentWeights($this->getSelectedProducts($order, $request));
        if ($shipmentGroups === []) {
            return [];
        }

        $rajaOngkir = new \App\Services\RajaOngkirService();
        $couriers = array_values(array_unique(array_merge(...array_map(fn ($group) => $group['couriers'] ?? [], $shipmentGroups))));
        $optionsByCode = [];
        $shipmentCount = count($shipmentGroups);

        foreach ($couriers as $courier) {
            foreach ($shipmentGroups as $group) {
                if (!in_array($courier, $group['couriers'] ?? [], true)) {
                    continue;
                }

                $res = $rajaOngkir->calculateDomesticCost($group['origin_id'], $destinationId, max(1, $group['weight_grams']), $courier);
                foreach (($res['costs'] ?? []) as $costItem) {
                    $service = strtolower($costItem['service'] ?? 'reg');
                    $code = $courier . '_' . $service;
                    $costVal = (float) ($costItem['cost'][0]['value'] ?? 0);
                    if ($costVal <= 0) {
                        continue;
                    }

                    if (!isset($optionsByCode[$code])) {
                        $optionsByCode[$code] = [
                            'code' => $code,
                            'name' => ($res['name'] ?? strtoupper($courier)) . ' (' . strtoupper($service) . ')',
                            'price' => 0.0,
                            'shipments' => 0,
                        ];
                    }
                    $optionsByCode[$code]['price'] += $costVal;
                    $optionsByCode[$code]['shipments'] += 1;
                }
            }
        }

        return array_values(array_map(function ($option) {
            unset($option['shipments']);
            return $option;
        }, array_filter($optionsByCode, fn ($option) => ($option['shipments'] ?? 0) === $shipmentCount)));
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

        \Illuminate\Support\Facades\DB::table('mshop_order_service')
            ->where('parentid', $order->getId())
            ->delete();

        \Illuminate\Support\Facades\DB::table('mshop_order')
            ->where('id', $order->getId())
            ->update(['costs' => 0]);

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

        // Aimeos enforces a unique key on parent/type/position. Replacing the
        // previous address first makes selecting an address idempotent.
        \Illuminate\Support\Facades\DB::table('mshop_order_address')
            ->where('parentid', $orderId)
            ->where('type', $type)
            ->delete();

        $addrItem = $addressManager->create();
        $addrItem->setParentId($orderId);
        $addrItem->setType($type);
        $addrItem->setPosition(0);

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

        if (\Illuminate\Support\Facades\Schema::hasColumn('mshop_customer_address', 'ro_city_id') && \Illuminate\Support\Facades\Schema::hasColumn('mshop_order_address', 'ro_city_id')) {
            $columns = ['ro_city_id', 'ro_subdistrict_id'];
            if (\Illuminate\Support\Facades\Schema::hasColumn('mshop_customer_address', 'komerce_destination_id')) {
                $columns[] = 'komerce_destination_id';
            }

            $location = \Illuminate\Support\Facades\DB::table('mshop_customer_address')
                ->where('id', $customerAddress->getId())
                ->first($columns);

            if ($location) {
                $update = [
                    'ro_city_id' => $location->ro_city_id,
                    'ro_subdistrict_id' => $location->ro_subdistrict_id,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('mshop_order_address', 'komerce_destination_id')) {
                    $update['komerce_destination_id'] = $location->komerce_destination_id ?? null;
                }

                \Illuminate\Support\Facades\DB::table('mshop_order_address')
                    ->where('parentid', $orderId)
                    ->where('type', $type)
                    ->where('pos', 0)
                    ->update($update);
            }
        }
    }

    /**
     * Get shipping options using RajaOngkir calculator
     */
    public function getShippingOptions(Request $request)
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

        try {
            $options = $this->calculateShippingOptions($order, $request, $deliveryAddress);
        } catch (\RuntimeException $e) {
            \Illuminate\Support\Facades\Log::warning('Checkout shipping options unavailable: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
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

        $context = $this->getContextWithLocale();
        $addressManager = \Aimeos\MShop::create($context, 'order/address');
        $filter = $addressManager->filter(true)->add([
            'order.address.parentid' => $order->getId(),
            'order.address.type'     => 'delivery',
        ]);
        $deliveryAddress = $addressManager->search($filter)->first();

        try {
            $options = $this->calculateShippingOptions($order, $request, $deliveryAddress);
        } catch (\RuntimeException $e) {
            \Illuminate\Support\Facades\Log::warning('Checkout shipping selection unavailable: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $selectedOption = collect($options)->firstWhere('code', $request->shipping_code);
        if ($selectedOption) {
            $price = (float) $selectedOption['price'];
            $name = $selectedOption['name'];
        } else {
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

        // Aimeos enforces a unique key on parent/type/position. Replacing the
        // previous service first makes selecting shipping/payment idempotent.
        \Illuminate\Support\Facades\DB::table('mshop_order_service')
            ->where('parentid', $orderId)
            ->where('type', $type)
            ->delete();

        $serviceItem = $serviceManager->create();
        $serviceItem->setParentId($orderId);
        $serviceItem->setType($type);
        $serviceItem->setPosition(0);

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

        $selectedPositions = $this->selectedPositions($request);
        if (!empty($selectedPositions)) {
            $selectedProducts = $this->getSelectedProducts($order, $request);
            if (count($selectedProducts) === 0) {
                return response()->json(['message' => 'Pilih minimal satu produk untuk checkout.'], 400);
            }

            \Illuminate\Support\Facades\DB::table('mshop_order_product')
                ->where('parentid', $order->getId())
                ->whereNotIn('pos', $selectedPositions)
                ->delete();

            $order = $this->getOrderForUser();
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

        // Save app_service_fee service item
        $appServiceFee = (float) \App\Models\SystemSetting::getVal('app_service_fee', 2000);
        $this->upsertOrderService($context, $order->getId(), 'service', 'app_service_fee', 'Biaya Layanan Aplikasi', $appServiceFee);

        // Fetch shipping cost to compute total costs (shipping + app service fee)
        $shippingPrice = 0.0;
        $services = \DB::table('mshop_order_service')->where('parentid', $order->getId())->get();
        foreach ($services as $srv) {
            if ($srv->type === 'delivery') {
                $shippingPrice = (float) $srv->price;
            }
        }
        $totalCosts = $shippingPrice + $appServiceFee;

        // Update costs column of mshop_order in database
        \DB::table('mshop_order')
            ->where('id', $order->getId())
            ->update([
                'statuspayment' => OrderBase::PAY_PENDING,
                'statusdelivery' => OrderBase::STAT_PENDING,
                'costs' => $totalCosts,
            ]);

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

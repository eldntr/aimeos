<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Aimeos\MShop\Order\Item\Base as OrderBase;

/**
 * Class CartController
 *
 * Handles cart controller operations for the application.
 */
class CartController extends Controller
{
    /**
     * Get initialized Aimeos context with locale
     */
    private function getContextWithLocale()
    {
        $context = app('aimeos.context')->get(false);
        
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', 'IDR', false);
        
        // Bypass Aimeos' broken site item fetching during tests by force-injecting a mock site item
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
     * Get the Basket frontend controller for the authenticated user.
     */
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

        // Force inject the mock Site Item into the Order's Locale!
        // When an order is loaded via search(), its Locale is bare and lacks the Site Item.
        // Aimeos Basket Controller checkLocale() will crash if the Order Locale lacks it.
        $orderLocale = $order->locale();
        $ref = new \ReflectionClass($orderLocale);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($orderLocale, $context->locale()->getSiteItem());
        }

        // Set the order in the manager's session cache manually so the Basket controller uses it
        $orderManager->setSession($order, 'default');
        
        // Disable local decorators (like Category, Bundle) which conflict in headless APIs
        $context->config()->set('controller/frontend/basket/decorators/local', []);
        
        return \Aimeos\Controller\Frontend::create($context, 'basket');
    }

    /**
     * Persist the basket to the database.
     */
    private function saveBasket($basketController)
    {
        $context = $this->getContextWithLocale();
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $order = $basketController->get();
        
        if (auth()->check()) {
            $order->setCustomerId((string) auth()->user()->id);
        }
        
        // Save to database
        $orderManager->begin();
        $savedOrderId = null;
        $savedOrderPrice = 0;
        try {
            $existingOrderId = $order->getId();

            if ($existingOrderId) {
                \Illuminate\Support\Facades\DB::table('mshop_order_product')
                    ->where('parentid', $existingOrderId)
                    ->delete();

                \Illuminate\Support\Facades\DB::table('mshop_order_address')
                    ->where('parentid', $existingOrderId)
                    ->delete();

                \Illuminate\Support\Facades\DB::table('mshop_order_service')
                    ->where('parentid', $existingOrderId)
                    ->delete();
            }

            if ($existingOrderId) {
                $orderProductManager = \Aimeos\MShop::create($context, 'order/product');

                foreach ($order->getProducts() as $pos => $productItem) {
                    $productItem->setParentId($existingOrderId);
                    $productItem->setPosition((int) $pos);
                    $productItem->setId(null);
                    $orderProductManager->save($productItem);
                }
            } else {
                $orderManager->save($order);
            }

            $savedOrderId = $order->getId();
            $savedOrderPrice = (float) $order->getPrice()->getValue();
            
            $orderManager->commit();
        } catch (\Exception $e) {
            $orderManager->rollback();
            throw $e;
        }

        if ($savedOrderId) {
            \Illuminate\Support\Facades\DB::table('mshop_order_address')
                ->where('parentid', $savedOrderId)
                ->delete();

            \Illuminate\Support\Facades\DB::table('mshop_order_service')
                ->where('parentid', $savedOrderId)
                ->delete();

            \Illuminate\Support\Facades\DB::table('mshop_order')
                ->where('id', $savedOrderId)
                ->update([
                    'price' => $savedOrderPrice,
                    'costs' => 0,
                ]);
        }
    }

    /**
     * Check if adding the specified quantity will exceed the 99 items limit.
     */
    private function checkQuantityLimit($basket, $additionalQuantity = 0, $positionToIgnore = null)
    {
        $total = 0;
        foreach ($basket->getProducts() as $pos => $product) {
            if ($positionToIgnore !== null && $pos == $positionToIgnore) {
                continue;
            }
            $total += $product->getQuantity();
        }
        
        if (($total + $additionalQuantity) > 99) {
            abort(422, 'Maksimal total item di keranjang adalah 99.');
        }
    }

    /**
     * Serialize basket with products
     */
    private function formatBasketResponse($basketController)
    {
        $basket = $basketController->get();
        $data = $basket->toArray();
        $data['product'] = [];
        
        foreach ($basket->getProducts() as $pos => $productItem) {
            $data['product'][$pos] = $productItem->toArray();
        }

        // Include applied coupons/vouchers
        $data['coupon'] = [];
        foreach ($basket->getCoupons() as $code => $couponItem) {
            $data['coupon'][] = [
                'code' => $code,
                'name' => $couponItem->getName(),
            ];
        }

        $data['app_service_fee'] = (float) \App\Models\SystemSetting::getVal('app_service_fee', 2000);
        
        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $basketController = $this->getBasketController();
        
        return response()->json([
            'data' => $this->formatBasketResponse($basketController)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'quantity'   => 'numeric|min:1',
        ]);

        $quantity = $request->input('quantity', 1);
        $basketController = $this->getBasketController();
        $basket = $basketController->get();

        $this->checkQuantityLimit($basket, $quantity);

        $context = $this->getContextWithLocale();
        $productManager = \Aimeos\MShop::create($context, 'product');
        
        try {
            $product = $productManager->get($request->product_id);
            $siteId = $product->getSiteId();
            
            $parts = array_filter(explode('.', trim($siteId, '.')));
            $numericId = end($parts);
            $siteCode = 'default';
            if ($numericId) {
                $siteManager = \Aimeos\MShop::create($context, 'locale/site');
                $filter = $siteManager->filter()->add(['locale.site.id' => (int) $numericId]);
                $site = $siteManager->search($filter)->first();
                if ($site) {
                    $siteCode = $site->getCode();
                }
            }

            if ($siteCode !== 'default') {
                $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
                $context->setLocale($locale);
            }

            $nativeProductManager = \Aimeos\MShop::create($context, 'product');
            $product = $nativeProductManager->get($request->product_id, ['attribute', 'media', 'price', 'text', 'catalog', 'product']);
            
            if ($product->getType() === 'select') {
                $variantIds = [];
                foreach ($product->getListItems('product', 'default') as $listItem) {
                    $variantIds[] = $listItem->getRefId();
                }
                if (!empty($variantIds)) {
                    $filter = $nativeProductManager->filter();
                    $filter->add($filter->compare('==', 'product.id', $variantIds));
                    $filter->add($filter->compare('==', 'product.status', 1));
                    $firstVariant = $nativeProductManager->search($filter, ['attribute', 'media', 'price', 'text', 'catalog'])->first();
                    if ($firstVariant) {
                        if ($firstVariant->getListItems('price')->isEmpty()) {
                            foreach ($product->getListItems('price') as $priceList) {
                                $firstVariant->addListItem('price', $priceList, $priceList->getRefItem());
                            }
                        }
                        $product = $firstVariant;
                    }
                }
            }
            
            if ($siteCode !== 'default') {
                $context->setLocale($this->getContextWithLocale()->locale());
            }

            $existingPos = null;
            foreach ($basket->getProducts() as $pos => $productItem) {
                if ($productItem->getProductId() == $request->product_id) {
                    $existingPos = $pos;
                    break;
                }
            }

            if ($existingPos !== null) {
                $basketController->updateProduct((int) $existingPos, $quantity);
            } else {
                $basketController->addProduct($product, $quantity);
            }
            $this->saveBasket($basketController);

            return response()->json([
                'message' => 'Produk berhasil ditambahkan ke keranjang.',
                'data' => $this->formatBasketResponse($basketController)
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cart Add Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $position)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        $quantity = $request->input('quantity');
        $basketController = $this->getBasketController();
        $basket = $basketController->get();

        // Pass $position to ignore its current quantity in the calculation
        $this->checkQuantityLimit($basket, $quantity, $position);

        try {
            $basketController->updateProduct((int) $position, $quantity);
            $this->saveBasket($basketController);

            return response()->json([
                'message' => 'Kuantitas berhasil diperbarui.',
                'data' => $this->formatBasketResponse($basketController)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($position)
    {
        $basketController = $this->getBasketController();
        
        try {
            $basketController->deleteProduct((int) $position);
            $this->saveBasket($basketController);

            return response()->json([
                'message' => 'Produk berhasil dihapus dari keranjang.',
                'data' => $this->formatBasketResponse($basketController)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        $context = $this->getContextWithLocale();
        $user = auth()->user();
        
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $filter = $orderManager->filter();
        $filter->add($filter->and([
            $filter->compare('==', 'order.customerid', $user->id),
            $filter->compare('==', 'order.statuspayment', OrderBase::PAY_UNFINISHED),
            $filter->compare('==', 'order.statusdelivery', OrderBase::STAT_UNFINISHED),
        ]));
        $filter->order('-order.id')->slice(0, 1);
        
        $order = $orderManager->search($filter)->first();
        
        if ($order && $order->getId()) {
            $basketId = $order->getId();
            
            // Delete products first to avoid foreign key constraints
            \Illuminate\Support\Facades\DB::table('mshop_order_product')
                ->where('parentid', $basketId)
                ->delete();
            
            \Illuminate\Support\Facades\DB::table('mshop_order_address')
                ->where('parentid', $basketId)
                ->delete();
                
            \Illuminate\Support\Facades\DB::table('mshop_order_service')
                ->where('parentid', $basketId)
                ->delete();
                
            \Illuminate\Support\Facades\DB::table('mshop_order_coupon')
                ->where('parentid', $basketId)
                ->delete();
                
            \Illuminate\Support\Facades\DB::table('mshop_order_status')
                ->where('parentid', $basketId)
                ->delete();
                
            // Bypass Aimeos Cache Manager to prevent 'No site item available' exceptions during eviction
            \Illuminate\Support\Facades\DB::table('mshop_order')
                ->where('id', $basketId)
                ->delete();
        }
        
        $basketController = $this->getBasketController();
        $basketController->clear();

        return response()->json([
            'message' => 'Keranjang berhasil dikosongkan.',
            'data' => $this->formatBasketResponse($basketController)
        ]);
    }

    /**
     * Apply a voucher/coupon code to the cart.
     */
    public function applyVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $basketController = $this->getBasketController();
        
        try {
            $basketController->addCoupon($request->code);
            $this->saveBasket($basketController);

            return response()->json([
                'message' => 'Voucher berhasil diterapkan.',
                'data' => $this->formatBasketResponse($basketController)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Voucher tidak valid atau kadaluarsa.'], 422);
        }
    }

    /**
     * Remove a voucher/coupon from the cart.
     */
    public function removeVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $basketController = $this->getBasketController();
        
        try {
            $basketController->deleteCoupon($request->code);
            $this->saveBasket($basketController);

            return response()->json([
                'message' => 'Voucher berhasil dihapus.',
                'data' => $this->formatBasketResponse($basketController)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

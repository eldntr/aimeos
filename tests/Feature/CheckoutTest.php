<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class CheckoutTest extends TestCase
{

    // Aimeos testing setup requires this
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\DB::table('mshop_locale_site')
            ->where('siteid', '1.')
            ->update([
                'config' => json_encode([
                    'shipping.komerce_destination_id' => '110',
                    'shipping.origin_id' => '110',
                    'shipping.city' => 'Jakarta Barat',
                    'shipping.province' => 'DKI Jakarta',
                    'shipping.subdistrict' => 'Grogol Petamburan',
                    'shipping.postal' => '11470',
                    'address' => 'Jl. Tanjung Duren Raya No. 1',
                ])
            ]);
        
        \Illuminate\Support\Facades\DB::table('seller_shipping_couriers')
            ->where('siteid', '1.')
            ->delete();
        \Illuminate\Support\Facades\DB::table('seller_shipping_couriers')
            ->insert([
                ['siteid' => '1.', 'courier_code' => 'jne'],
                ['siteid' => '1.', 'courier_code' => 'jnt'],
                ['siteid' => '1.', 'courier_code' => 'sicepat'],
            ]);
        
        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $siteItem = $siteManager->create();
        $siteItem->setId('1.');
        $siteItem->setCode('default');
        
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        
        $ref = new \ReflectionClass($localeItem);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($localeItem, $siteItem);
        }
        $context->setLocale($localeItem);
    }

    private function createCustomerAndGetToken($email)
    {
        $response = $this->post('/api/register/customer', [
            'name' => 'Test Customer',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        return [
            'user' => User::where('email', $email)->first(),
            'token' => $response->json('access_token')
        ];
    }

    private function setupProduct($label = 'Test Product Checkout')
    {
        $context = app('aimeos.context')->get(false);
        $manager = \Aimeos\MShop::create($context, 'product');
        
        $item = $manager->create()
            ->setCode('test-checkout-' . uniqid())
            ->setLabel($label)
            ->setType('default')
            ->setStatus(1);
            
        $listManager = \Aimeos\MShop::create($context, 'product/lists');
        
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        $currencyId = $localeItem->getCurrencyId();

        $priceManager = \Aimeos\MShop::create($context, 'price');
        $priceItem = $priceManager->create()->setCurrencyId($currencyId)->setValue('150000.00');
        $priceList = $listManager->create()->setDomain('price')->setType('default');
        $item->addListItem('price', $priceList, $priceItem);
            
        $manager->save($item);
        
        $stockManager = \Aimeos\MShop::create($context, 'stock');
        $stockItem = $stockManager->create()
            ->setProductId($item->getId())
            ->setType('default')
            ->setStockLevel(100);
        $stockManager->save($stockItem);
        
        return $item;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_full_checkout_flow()
    {
        $customer = $this->createCustomerAndGetToken('customer.checkout.' . uniqid() . '@example.com');
        $product = $this->setupProduct();

        \Laravel\Sanctum\Sanctum::actingAs($customer['user']);

        // 1. Add to cart
        $response = $this->postJson('/api/cart', [
            'product_id' => $product->getId(),
            'quantity' => 2
        ]);
        
        if ($response->status() !== 201) {
            dump($response->content());
        }
        
        $response->assertStatus(201);

        // 2. Add Address to Address Book
        $addressResponse = $this->postJson('/api/user/addresses', [
            'firstname' => 'Budi',
            'lastname' => 'Santoso',
            'telephone' => '08123456789',
            'address1' => 'Jl. Sudirman No 1',
            'city' => 'Jakarta',
            'postal' => '12345',
        ]);
        $addressResponse->assertStatus(201);
        $addressData = $addressResponse->json('data');
        $addressId = $addressData['id'] ?? $addressData['customer.address.id'] ?? null;
        if (!$addressId) {
            dump($addressData);
        }
        $this->assertNotNull($addressId);

        // 3. Select Address for Checkout
        $this->postJson('/api/checkout/address', [
            'address_id' => $addressId
        ])->assertStatus(200);

        // Changing cart after selecting address should clear stale checkout data
        // instead of re-saving duplicate order address/service rows.
        $this->postJson('/api/cart', [
            'product_id' => $product->getId(),
            'quantity' => 2
        ])->assertStatus(201);

        $draftOrderId = \DB::table('mshop_order')
            ->where('customerid', $customer['user']->id)
            ->where('statuspayment', \Aimeos\MShop\Order\Item\Base::PAY_UNFINISHED)
            ->where('statusdelivery', \Aimeos\MShop\Order\Item\Base::STAT_UNFINISHED)
            ->value('id');

        $this->assertDatabaseMissing('mshop_order_address', [
            'parentid' => $draftOrderId,
            'type' => 'delivery',
        ]);

        $this->postJson('/api/checkout/address', [
            'address_id' => $addressId
        ])->assertStatus(200);

        // 4. Get Shipping Options
        $shippingResponse = $this->getJson('/api/checkout/shipping');
        $shippingResponse->assertStatus(200);
        $this->assertCount(3, $shippingResponse->json('data'));

        // 5. Select Shipping Method
        $shippingSubmitResponse = $this->postJson('/api/checkout/shipping', [
            'shipping_code' => 'jnt_ez'
        ]);
        
        if ($shippingSubmitResponse->status() !== 200) {
            dump($shippingSubmitResponse->content());
        }
        $shippingSubmitResponse->assertStatus(200);

        // 6. Get Payment Options
        $paymentResponse = $this->getJson('/api/checkout/payment');
        $paymentResponse->assertStatus(200);
        $this->assertCount(3, $paymentResponse->json('data'));

        // 7. Select Payment Method
        $this->postJson('/api/checkout/payment', [
            'payment_code' => 'gopay'
        ])->assertStatus(200);

        // 8. Process Checkout
        $processResponse = $this->postJson('/api/checkout/process');
        
        if ($processResponse->status() !== 201) {
            dump($processResponse->content());
        }
        
        $processResponse->assertStatus(201);
        
        $this->assertEquals('pending', $processResponse->json('data.status'));
        $this->assertStringContainsString('midtrans', $processResponse->json('data.payment_url'));

        $orderId = $processResponse->json('data.order_id');
        $this->assertDatabaseHas('mshop_order_service', [
            'parentid' => $orderId,
            'type' => 'service',
            'code' => 'app_service_fee',
        ]);

        $this->assertEquals(
            2000,
            (float) \DB::table('mshop_order_service')
                ->where('parentid', $orderId)
                ->where('type', 'service')
                ->where('code', 'app_service_fee')
                ->value('price')
        );

    }
}

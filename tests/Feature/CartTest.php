<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists('\Aimeos\MShop')) {
            \Aimeos\MShop::cache(false);
            \Aimeos\MShop::cache(true);
        }
    }
    private function registerUserAndGetToken($email = 'cartuser@example.com')
    {
        $response = $this->postJson('/api/register/customer', [
            'name'                  => 'Cart User',
            'email'                 => $email,
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);
        
        $response->assertStatus(201);
        $user = User::where('email', $email)->first();
        // Fallback to manual token creation if endpoint didn't provide one
        $token = $response->json('data.token') ?? $user->createToken('test')->plainTextToken;
        
        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    private function getAvailableProductId()
    {
        $context = app('aimeos.context')->get(false);
        $manager = \Aimeos\MShop::create($context, 'product');
        
        $item = $manager->create()
            ->setCode('dummy-' . uniqid())
            ->setLabel('Dummy')
            ->setType('default')
            ->setStatus(1);
            
        $listManager = \Aimeos\MShop::create($context, 'product/lists');
        
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        $currencyId = $localeItem->getCurrencyId();

        $priceManager = \Aimeos\MShop::create($context, 'price');
        $priceItem = $priceManager->create()->setCurrencyId($currencyId)->setValue('10.00');
        $priceList = $listManager->create()->setDomain('price')->setType('default');
        $item->addListItem('price', $priceList, $priceItem);
            
        $manager->save($item);
        
        $stockManager = \Aimeos\MShop::create($context, 'stock');
        $stockItem = $stockManager->create()
            ->setProductId($item->getId())
            ->setType('default')
            ->setStockLevel(100);
        $stockManager->save($stockItem);
        
        return $item->getId();
    }

    public function test_user_can_add_product_to_cart()
    {
        $auth = $this->registerUserAndGetToken('cart_add@example.com');
        $productId = $this->getAvailableProductId();

        $response = $this->withToken($auth['token'])->postJson('/api/cart', [
            'product_id' => (string) $productId,
            'quantity'   => 1
        ]);

        if ($response->status() !== 201) {
            dump($response->json());
        }
        
        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'data' => ['product']]);
                 
        dump('STORE RESPONSE PRODUCTS:', $response->json('data.product'));

        // Verify it was persisted by retrieving the cart via API
        $verifyResponse = $this->withToken($auth['token'])->getJson('/api/cart');
        $verifyResponse->assertStatus(200);
        
        $products = $verifyResponse->json('data.product');
        $this->assertNotEmpty($products, "Unfinished order product should exist for customer.");
        
        $productsList = array_values($products);
        $this->assertEquals($productId, $productsList[0]['order.product.productid']);
    }

    public function test_user_cannot_add_more_than_99_items()
    {
        $auth = $this->registerUserAndGetToken('cart_limit@example.com');
        $productId = $this->getAvailableProductId();

        $response = $this->withToken($auth['token'])->postJson('/api/cart', [
            'product_id' => (string) $productId,
            'quantity'   => 100
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Maksimal total item di keranjang adalah 99.']);
    }

    public function test_user_can_view_cart()
    {
        $auth = $this->registerUserAndGetToken('cart_view@example.com');
        $productId = $this->getAvailableProductId();

        $this->withToken($auth['token'])->postJson('/api/cart', [
            'product_id' => (string) $productId,
            'quantity'   => 2
        ]);

        $response = $this->withToken($auth['token'])->getJson('/api/cart');
        
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.product'));
    }

    public function test_user_can_clear_cart()
    {
        $auth = $this->registerUserAndGetToken('cart_clear@example.com');
        $productId = $this->getAvailableProductId();

        $this->withToken($auth['token'])->postJson('/api/cart', [
            'product_id' => (string) $productId,
            'quantity'   => 1
        ]);

        $response = $this->withToken($auth['token'])->deleteJson('/api/cart');
        
        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.product'));

        $order = DB::table('mshop_order')
            ->where('customerid', $auth['user']->id)
            ->where('statuspayment', -1)
            ->first();
            
        $this->assertNull($order, "Unfinished order should be deleted.");
    }
}

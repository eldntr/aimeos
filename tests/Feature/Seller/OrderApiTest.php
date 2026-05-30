<?php

namespace Tests\Feature\Seller;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Feature\Concerns\SellerTestHelpers;

class OrderApiTest extends \Tests\TestCase
{
    use SellerTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFakeUploads();
    }

    public function test_seller_can_manage_orders(): void
    {
        $sellerInfo = $this->registerSellerAndGetToken('orders-test');
        $sellerToken = $sellerInfo['token'];
        $user = $sellerInfo['user'];
        
        // Create an order directly in DB for this seller
        $parts = array_filter(explode('.', trim($user->siteid, '.')));
        $numericId = end($parts);
        
        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $siteItem = $siteManager->get($numericId);
        $context->setLocale(app('aimeos.locale')->get($context, $siteItem->getCode()));
        
        $orderManager = \Aimeos\MShop::create($context, 'order');
        $order = $orderManager->create()->setStatusPayment(4); // 4 = PAY_PENDING (greater than -1 PAY_UNFINISHED)
        $order = $orderManager->save($order);
        $orderId = $order->getId();

        // 1. Get orders
        $resp = $this->getJson('/api/seller/orders', ['Authorization' => "Bearer $sellerToken"]);
        $resp->assertStatus(200);
        $this->assertNotEmpty($resp->json('data'));
        
        // 2. Get specific order
        $resp = $this->getJson("/api/seller/orders/$orderId", ['Authorization' => "Bearer $sellerToken"]);
        $resp->assertStatus(200);
        
        // 3. Update status (accept)
        $resp = $this->patchJson("/api/seller/orders/$orderId/status", [
            'status' => 'accept',
            'tracking_number' => 'RESI123'
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        $resp->assertStatus(200);
        $this->assertEquals('RESI123', $resp->json('data.tracking_number'));
        $this->assertEquals(3, $resp->json('data.status_delivery')); // 3 = STAT_DISPATCHED
        
        // 4. Request pickup
        $resp = $this->postJson("/api/seller/orders/$orderId/pickup", [], ['Authorization' => "Bearer $sellerToken"]);
        $resp->assertStatus(200);
    }

}

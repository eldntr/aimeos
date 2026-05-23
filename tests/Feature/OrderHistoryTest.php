<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Aimeos\MShop;
use Aimeos\MShop\Order\Item\Base;

class OrderHistoryTest extends TestCase
{
    protected $user;
    protected $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset Aimeos cache for test isolation
        MShop::cache(false);
        MShop::cache(true);

        $this->user = User::factory()->create([
            'email' => 'history@example.com'
        ]);
        
        $this->otherUser = User::factory()->create([
            'email' => 'other@example.com'
        ]);
    }

    private function createOrderForUser($userId, $statusPayment)
    {
        $context = app('aimeos.context')->get();
        $orderManager = MShop::create($context, 'order');
        
        $order = $orderManager->create();
        $order->setCustomerId((string) $userId);
        $order->setStatusPayment($statusPayment);
        $order->setStatusDelivery(Base::STAT_PENDING);
        
        return $orderManager->save($order);
    }

    public function test_user_can_view_order_history()
    {
        // Buat 1 order pending dan 1 order unfinished (cart)
        $this->createOrderForUser($this->user->id, Base::PAY_PENDING);
        $this->createOrderForUser($this->user->id, Base::PAY_UNFINISHED);
        
        // Buat 1 order pending untuk user lain
        $this->createOrderForUser($this->otherUser->id, Base::PAY_PENDING);

        $response = $this->actingAs($this->user)->getJson('/api/user/orders');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data') // Hanya boleh 1 (karena yg unfinished dan punya org lain diabaikan)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         '*' => ['id', 'date', 'status_payment', 'status_delivery', 'price', 'currency']
                     ]
                 ]);
                 
        $this->assertEquals(Base::PAY_PENDING, $response->json('data.0.status_payment'));
    }

    public function test_user_can_view_order_detail()
    {
        $order = $this->createOrderForUser($this->user->id, Base::PAY_PENDING);

        $response = $this->actingAs($this->user)->getJson('/api/user/orders/' . $order->getId());

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id', 'date', 'payment_url', 'products', 'addresses', 'services'
                     ]
                 ]);
                 
        $this->assertNotNull($response->json('data.payment_url'));
    }

    public function test_user_cannot_view_other_user_order()
    {
        $order = $this->createOrderForUser($this->otherUser->id, Base::PAY_PENDING);

        $response = $this->actingAs($this->user)->getJson('/api/user/orders/' . $order->getId());

        $response->assertStatus(403);
    }
}

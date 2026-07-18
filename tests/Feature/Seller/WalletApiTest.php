<?php

namespace Tests\Feature\Seller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Feature\Concerns\SellerTestHelpers;
use App\Models\User;
use App\Models\SellerWithdrawal;
use Aimeos\MShop;

class WalletApiTest extends TestCase
{
    use SellerTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        SellerWithdrawal::query()->delete();
    }

    public function test_seller_can_get_wallet_balance()
    {
        $unique = uniqid();
        $sellerData = $this->registerSellerAndGetToken('wallet-' . $unique);
        $seller = $sellerData['user'];
        $code = $sellerData['code'];

        // At first, balance should be 0 because we have no orders
        $response = $this->actingAs($seller, 'sanctum')->get('/api/seller/wallet');
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.available_balance', 0)
                 ->assertJsonPath('data.total_revenue', 0);
                 
        $siteId = \DB::table('mshop_locale_site')->where('code', $code)->value('siteid');
        
        \DB::table('mshop_order')->insert([
            'siteid' => $siteId,
            'price' => 150000.00,
            'statuspayment' => \Aimeos\MShop\Order\Item\Base::PAY_AUTHORIZED,
            'ctime' => date('Y-m-d H:i:s'),
            'mtime' => date('Y-m-d H:i:s'),
            'editor' => 'test',
            'comment' => '',
            'currencyid' => 'EUR', // default for aimeos test
        ]);
        
        // Check balance again
        $response2 = $this->actingAs($seller, 'sanctum')->get('/api/seller/wallet');
        $response2->assertStatus(200)
                 ->assertJsonPath('data.total_revenue', 142500)
                 ->assertJsonPath('data.available_balance', 142500);
    }

    public function test_seller_can_withdraw_funds()
    {
        $unique = uniqid();
        $sellerData = $this->registerSellerAndGetToken('withdraw-' . $unique);
        $seller = $sellerData['user'];
        $code = $sellerData['code'];

        // Insufficient balance
        $response = $this->actingAs($seller, 'sanctum')->post('/api/seller/withdraw', [
            'amount' => 50000
        ]);
        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Saldo tidak mencukupi untuk melakukan penarikan.');
                 
        // Add fake revenue
        $siteId = \DB::table('mshop_locale_site')->where('code', $code)->value('siteid');
        
        \DB::table('mshop_order')->insert([
            'siteid' => $siteId,
            'price' => 150000.00,
            'statuspayment' => \Aimeos\MShop\Order\Item\Base::PAY_AUTHORIZED,
            'ctime' => date('Y-m-d H:i:s'),
            'mtime' => date('Y-m-d H:i:s'),
            'editor' => 'test',
            'comment' => '',
            'currencyid' => 'EUR', // default for aimeos test
        ]);
        
        // Try again with sufficient balance
        $response2 = $this->actingAs($seller, 'sanctum')->post('/api/seller/withdraw', [
            'amount' => 50000
        ]);
        $response2->assertStatus(201)
                  ->assertJsonPath('message', 'Permintaan penarikan dana berhasil diajukan.');
                  
        $this->assertDatabaseHas('seller_withdrawals', [
            'siteid' => $seller->siteid,
            'amount' => 50000,
            'status' => 'pending'
        ]);
        
        // Check wallet again to see if pending reduces balance
        $response3 = $this->actingAs($seller, 'sanctum')->get('/api/seller/wallet');
        $response3->assertStatus(200)
                  ->assertJsonPath('data.available_balance', 92500)
                  ->assertJsonPath('data.pending_withdrawal', 50000);
    }
}

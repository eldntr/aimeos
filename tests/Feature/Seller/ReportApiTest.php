<?php

namespace Tests\Feature\Seller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Feature\Concerns\SellerTestHelpers;
use Aimeos\MShop;

class ReportApiTest extends TestCase
{
    use SellerTestHelpers;

    public function test_seller_can_get_sales_report()
    {
        $unique = uniqid();
        $sellerData = $this->registerSellerAndGetToken('report-' . $unique);
        $seller = $sellerData['user'];
        $code = $sellerData['code'];

        // Fetch empty report
        $response = $this->actingAs($seller, 'sanctum')->get('/api/seller/reports/sales');
        $response->assertStatus(200)
                 ->assertJsonPath('data.summary.total_revenue', 0)
                 ->assertJsonPath('data.summary.total_orders', 0);
                 
        // Add fake order
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
        
        // Fetch report again
        $response2 = $this->actingAs($seller, 'sanctum')->get('/api/seller/reports/sales');
        $response2->assertStatus(200)
                  ->assertJsonPath('data.summary.total_revenue', 150000)
                  ->assertJsonPath('data.summary.total_orders', 1)
                  ->assertJsonStructure([
                      'data' => [
                          'summary' => ['total_revenue', 'total_orders'],
                          'daily_sales' => [
                              '*' => ['date', 'revenue', 'orders_count']
                          ]
                      ]
                  ]);
    }
}

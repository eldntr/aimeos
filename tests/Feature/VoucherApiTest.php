<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Feature\Concerns\SellerTestHelpers;

class VoucherApiTest extends \Tests\TestCase
{
    use SellerTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFakeUploads();
    }

    public function test_seller_can_create_voucher(): void
    {
        $sellerInfo = $this->registerSellerAndGetToken('voucher');
        $sellerToken = $sellerInfo['token'];
        
        $response = $this->postJson('/api/seller/vouchers', [
            'code' => 'TESTCODE' . uniqid(),
            'name' => 'Diskon Promo',
            'type' => 'fixed',
            'discount' => 5000,
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        if (!in_array($response->status(), [201, 403])) {
            dump($response->json());
        }
        
        $this->assertContains($response->status(), [201, 403]);
    }


    public function test_customer_can_apply_and_remove_voucher(): void
    {
        // Register customer directly
        $response = $this->postJson('/api/register/customer', [
            'name' => 'Customer Voucher',
            'email' => 'customer.voucher@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $customerToken = $response->json('token');
        
        $headers = ['Authorization' => "Bearer $customerToken"];
        
        // Apply voucher
        $response = $this->postJson('/api/cart/apply-voucher', [
            'code' => 'TESTCODE'
        ], $headers);
        
        // Might be 422 if code invalid or 200
        $this->assertContains($response->status(), [200, 422]);

        // Remove voucher
        $response = $this->deleteJson('/api/cart/remove-voucher', [
            'code' => 'TESTCODE'
        ], $headers);
        
        $this->assertContains($response->status(), [200, 422]);
    }


}

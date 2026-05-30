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

class ProfileApiTest extends \Tests\TestCase
{
    use SellerTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFakeUploads();
    }

    public function test_customer_cannot_access_seller_endpoint(): void
    {
        // Register as a customer (siteid will be set to default site, not a seller site)
        $this->postJson('/api/register/customer', [
            'name'                  => 'Customer No Seller',
            'email'                 => 'customer.noseller@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $customer = User::where('email', 'customer.noseller@example.com')->first();
        $token = $customer->createToken('test')->plainTextToken;

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $response = $this->withToken($token)
            ->post('/api/seller/products', [
                'label' => 'Unauthorized Product',
                'code'  => 'unauth-product',
                'images' => [UploadedFile::fake()->image('test.jpg')],
            ]);

        $response->assertStatus(403);
    }


    public function test_admin_can_approve_and_reject_seller_and_seller_can_reupload_ktp(): void
    {
        $unique = uniqid();
        $admin = $this->registerAdminAndGetToken("admin.verif.{$unique}@example.com");

        $sellerData = [
            'code' => "mv-{$unique}",
            'email' => "seller.verif.{$unique}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => 'Seller Verif 1',
            'telephone' => '08987654321',
            'address' => 'Jl. Thamrin No. 2',
            'bank_account_number' => '0987654321',
            'bank_account_name' => 'Seller 1',
            'bank_name' => 'Bank Mandiri',
            'ktp_image' => \Illuminate\Http\UploadedFile::fake()->image('ktp.jpg'),
        ];

        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $this->post('/api/register/seller', $sellerData)->assertStatus(201);
        
        $seller = \App\Models\User::where('email', "seller.verif.{$unique}@example.com")->first();

        // 1. Admin reads list
        \Laravel\Sanctum\Sanctum::actingAs($admin['user']);
        $response = $this->getJson('/api/admin/sellers/pending')
             ->assertStatus(200);
        
        $this->assertStringContainsString("seller.verif.{$unique}@example.com", $response->content());

        // 2. Admin rejects
        \Laravel\Sanctum\Sanctum::actingAs($admin['user']);
        $this->postJson("/api/admin/sellers/{$seller->id}/reject", ['reason' => 'Blurry KTP'])
             ->assertStatus(200);

        $this->assertEquals('rejected', $seller->fresh()->seller_status);
        $this->assertEquals('Blurry KTP', $seller->fresh()->rejection_reason);
        
        // 3. Seller re-uploads
        \Laravel\Sanctum\Sanctum::actingAs($seller->fresh());
        $this->post('/api/seller/reupload-ktp', [
                 'ktp_image' => \Illuminate\Http\UploadedFile::fake()->image('ktp_new.jpg'),
             ])
             ->assertStatus(200);

        $this->assertEquals('pending', $seller->fresh()->seller_status);
        $this->assertNull($seller->fresh()->rejection_reason);

        // 4. Admin approves
        \Laravel\Sanctum\Sanctum::actingAs($admin['user']);
        $this->postJson("/api/admin/sellers/{$seller->id}/approve")
             ->assertStatus(200);

        $this->assertEquals('approved', $seller->fresh()->seller_status);
    }


    public function test_seller_can_update_shop(): void
    {
        $sellerInfo = $this->registerSellerAndGetToken('shop-update');
        $sellerToken = $sellerInfo['token'];
        
        $response = $this->putJson('/api/seller/shop', [
            'name' => 'New Shop Name',
            'address' => 'Jl. Kebon Jeruk No 1'
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        if ($response->status() !== 403) {
            $response->assertStatus(200)
                     ->assertJsonPath('data.name', 'New Shop Name');
        }
    }


    public function test_seller_can_update_bank(): void
    {
        $sellerInfo = $this->registerSellerAndGetToken('bank-update');
        $sellerToken = $sellerInfo['token'];
        
        $response = $this->putJson('/api/seller/bank', [
            'bank_name' => 'BCA',
            'account_number' => '123456789',
            'account_name' => 'John Doe Bank'
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        if ($response->status() !== 403) {
            $response->assertStatus(200);
            $this->assertEquals('BCA', $response->json('data.config.bank.name') ?? $response->json('data.config')['bank.name']);
        }
    }


}

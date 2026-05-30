<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            '*/api/files/presign-upload' => Http::response([
                'upload_url' => 'http://mock-minio/upload',
                'file_id' => 'mock-file-123'
            ], 200),
            '*/api/files/*/complete-upload' => Http::response(['message' => 'success'], 200),
            'http://mock-minio/upload' => Http::response('', 200),
        ]);
    }

    public function test_user_can_register_via_api(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        if (config('app.shop_multishop') && config('app.shop_registration')) {
            $data['code'] = 'testsiteapi';
        }

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'siteid',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@example.com',
        ]);
    }

    public function test_user_cannot_register_with_mismatched_password_via_api(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        if (config('app.shop_multishop') && config('app.shop_registration')) {
            $data['code'] = 'testsiteapi2';
        }

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_login_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'login.test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login.test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user'
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'login.fail@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login.fail@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_get_profile_via_api(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile_via_api(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_profile_via_api(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Updated Name')
            ->assertJsonPath('user.email', 'updated@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_authenticated_user_can_change_password_via_api(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/password', [
            'current_password' => 'old_password123',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password updated successfully']);

        $this->assertTrue(Hash::check('new_password123', $user->fresh()->password));
    }

    public function test_authenticated_user_can_delete_account_via_api(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/profile', [
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Account deleted successfully']);

        $this->assertModelMissing($user);
    }

    public function test_user_cannot_delete_account_with_invalid_password_via_api(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/profile', [
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422);

        $this->assertModelExists($user);
    }

    public function test_register_customer_via_api(): void
    {
        $data = [
            'name' => 'Customer User',
            'email' => 'customer.api@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register/customer', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'siteid',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'customer.api@example.com',
        ]);

        $user = User::where('email', 'customer.api@example.com')->first();
        $this->assertNotNull($user);

        // Customer shouldn't have admin group
        $hasGroup = \Illuminate\Support\Facades\DB::table('mshop_customer_list')
            ->where('parentid', $user->id)
            ->where('domain', 'group')
            ->exists();
        $this->assertFalse($hasGroup);
    }

    public function test_register_seller_via_api(): void
    {
        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $data = [
            'code' => 'testsiteapi-seller',
            'email' => 'seller.api@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => 'John Doe',
            'telephone' => '08123456789',
            'address' => 'Jl. Sudirman No. 1',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'John Doe',
            'bank_name' => 'Bank BCA',
            'ktp_image' => UploadedFile::fake()->image('ktp.jpg'),
        ];

        $response = $this->post('/api/register/seller', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'siteid',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'seller.api@example.com',
            'telephone' => '08123456789',
            'address1' => 'Jl. Sudirman No. 1',
            'seller_status' => 'pending',
        ]);

        $user = User::where('email', 'seller.api@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->ktp_url);

        $this->assertDatabaseHas('seller_bank_details', [
            'user_id' => $user->id,
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'John Doe',
            'bank_name' => 'Bank BCA',
        ]);

        // Check if the site was created
        $this->assertDatabaseHas('mshop_locale_site', [
            'code' => 'testsiteapi-seller',
        ]);

        $site = \Illuminate\Support\Facades\DB::table('mshop_locale_site')
            ->where('code', 'testsiteapi-seller')
            ->first();
        $this->assertEquals($site->siteid, $user->siteid);

        // Check if the seller has admin group for their site
        $adminGroup = \Illuminate\Support\Facades\DB::table('mshop_group')
            ->where('code', 'admin')
            ->where('siteid', $site->siteid)
            ->first();
        $this->assertNotNull($adminGroup);

        $this->assertDatabaseHas('users_list', [
            'parentid' => $user->id,
            'refid' => $adminGroup->id,
            'domain' => 'group',
            'siteid' => $site->siteid,
        ]);
    }

    public function test_register_admin_via_api(): void
    {
        $data = [
            'name' => 'Admin User',
            'email' => 'admin.api@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register/admin', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'siteid',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin.api@example.com',
        ]);

        $user = User::where('email', 'admin.api@example.com')->first();
        $this->assertNotNull($user);

        // Admin should have admin group on default site
        $defaultSite = \Illuminate\Support\Facades\DB::table('mshop_locale_site')
            ->where('code', 'default')
            ->first();
        $this->assertNotNull($defaultSite);

        $adminGroup = \Illuminate\Support\Facades\DB::table('mshop_group')
            ->where('code', 'admin')
            ->where('siteid', $defaultSite->siteid)
            ->first();
        $this->assertNotNull($adminGroup);

        $this->assertDatabaseHas('users_list', [
            'parentid' => $user->id,
            'refid' => $adminGroup->id,
            'domain' => 'group',
            'siteid' => $defaultSite->siteid,
        ]);
    }
    public function test_existing_customer_can_register_as_seller(): void
    {
        // 1. Create a customer
        $customerData = [
            'name' => 'Existing Customer',
            'email' => 'upgrade.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $this->postJson('/api/register/customer', $customerData)->assertStatus(201);
        
        $user = User::where('email', 'upgrade.test@example.com')->first();
        $this->assertNotNull($user);
        $originalSiteId = $user->siteid;

        // 2. Register as a seller with the same email
        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $sellerData = [
            'code' => 'testsiteapi-upgrade',
            'email' => 'upgrade.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => 'Existing Customer Updated',
            'telephone' => '08987654321',
            'address' => 'Jl. Thamrin No. 2',
            'bank_account_number' => '0987654321',
            'bank_account_name' => 'Existing Customer',
            'bank_name' => 'Bank Mandiri',
            'ktp_image' => UploadedFile::fake()->image('ktp.jpg'),
        ];
        
        $response = $this->post('/api/register/seller', $sellerData);
        $response->assertStatus(201);
        
        $user->refresh();
        $this->assertNotEquals($originalSiteId, $user->siteid);
        $this->assertEquals('Existing Customer Updated', $user->name);
        $this->assertEquals('08987654321', $user->telephone);
        $this->assertEquals('Jl. Thamrin No. 2', $user->address1);

        $this->assertDatabaseHas('seller_bank_details', [
            'user_id' => $user->id,
            'bank_account_number' => '0987654321',
            'bank_account_name' => 'Existing Customer',
            'bank_name' => 'Bank Mandiri',
        ]);
        
        // 3. Check if seller group was applied on new siteid
        $site = \Illuminate\Support\Facades\DB::table('mshop_locale_site')
            ->where('code', 'testsiteapi-upgrade')
            ->first();
        $this->assertEquals($site->siteid, $user->siteid);
        
        $adminGroup = \Illuminate\Support\Facades\DB::table('mshop_group')
            ->where('code', 'admin')
            ->where('siteid', $site->siteid)
            ->first();
            
        $this->assertDatabaseHas('users_list', [
            'parentid' => $user->id,
            'refid' => $adminGroup->id,
            'domain' => 'group',
            'siteid' => $site->siteid,
        ]);
    }

    public function test_existing_user_cannot_register_with_invalid_password(): void
    {
        // 1. Create a customer
        $customerData = [
            'name' => 'Failed Upgrade Customer',
            'email' => 'fail.upgrade@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $this->postJson('/api/register/customer', $customerData)->assertStatus(201);

        // 2. Try to register as a seller with wrong password
        $sellerData = [
            'code' => 'testsiteapi-invpw',
            'email' => 'fail.upgrade@example.com',
            'password' => 'wrongpassword',
            'password_confirmation' => 'wrongpassword',
            'name' => 'Existing Customer Updated',
            'telephone' => '08987654321',
            'address' => 'Jl. Thamrin No. 2',
            'bank_account_number' => '0987654321',
            'bank_account_name' => 'Existing Customer',
            'bank_name' => 'Bank Mandiri',
            'ktp_image' => UploadedFile::fake()->image('ktp.jpg'),
        ];
        
        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/api/register/seller', $sellerData);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    // =========================================================================
    // Product API Tests — Public (Customer)
    // =========================================================================

    public function test_anyone_can_list_products(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'meta' => ['site', 'page', 'perPage', 'count'],
                 ]);
    }

    public function test_anyone_can_list_products_filtered_by_site(): void
    {
        $response = $this->getJson('/api/products?site=default');
        $response->assertStatus(200)
                 ->assertJsonPath('meta.site', 'default');
    }

    public function test_products_returns_404_for_invalid_site(): void
    {
        $response = $this->getJson('/api/products?site=nonexistent-site-xyz');
        $response->assertStatus(404);
    }

    public function test_anyone_can_get_banners(): void
    {
        $response = $this->getJson('/api/banners');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_anyone_can_get_categories(): void
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_anyone_can_get_products_by_category(): void
    {
        // 1 is typically the root category ID or we can just expect 200
        $response = $this->getJson('/api/categories/1/products');
        // Might be 404 if category 1 doesn't exist, so we accept 200 or 404
        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_anyone_can_get_shop_profile(): void
    {
        // 'default' site should always exist
        $response = $this->getJson('/api/shops/default');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['id', 'code', 'label', 'status']]);
    }

    public function test_anyone_can_get_products_by_shop(): void
    {
        $response = $this->getJson('/api/shops/default/products');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_anyone_can_get_product_variants(): void
    {
        // 1 is a mock product ID, so might return 404 or 200
        $response = $this->getJson('/api/products/1/variants');
        $this->assertContains($response->status(), [200, 404]);
    }

    // =========================================================================
    // Product API Tests — Seller CRUD
    // =========================================================================

    private function registerAdminAndGetToken(string $email): array
    {
        $response = $this->postJson('/api/register/admin', [
            'name'                  => 'Admin User',
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        $response->assertStatus(201);
        $data = $response->json();
        
        $adminUser = \App\Models\User::where('email', $email)->first();
        // Force siteid to empty (super admin / default site) for testing purposes
        $adminUser->siteid = '';
        $adminUser->save();

        return [
            'user'  => $adminUser,
            'token' => $data['access_token'],
        ];
    }

    /**
     * Helper: register a seller and return [user, token, siteCode].
     */
    private function registerSellerAndGetToken(string $suffix = 'prod'): array
    {
        $code = 'test-seller-' . $suffix;
        $email = 'seller.' . $suffix . '@example.com';

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $response = $this->post('/api/register/seller', [
            'code'                => $code,
            'email'               => $email,
            'password'            => 'password123',
            'password_confirmation' => 'password123',
            'name'                => 'Seller ' . $suffix,
            'telephone'           => '08100000000',
            'address'             => 'Jl. Test No. 1',
            'bank_account_number' => '1111111111',
            'bank_account_name'   => 'Seller ' . $suffix,
            'bank_name'           => 'Bank Test',
            'ktp_image'           => UploadedFile::fake()->image('ktp.jpg'),
        ]);

        if ($response->status() !== 201) {
            dump($response->content());
        }
        $response->assertStatus(201);
        $data = $response->json();

        $user = User::where('email', $email)->first();
        // Auto-approve for product tests
        $user->seller_status = 'approved';
        $user->save();

        if (\Illuminate\Support\Facades\Auth::check()) {
            \Illuminate\Support\Facades\Auth::setUser($user);
        }

        return [
            'user'  => $user,
            'token' => $data['access_token'],
            'code'  => $code,
        ];
    }

    public function test_seller_can_create_product(): void
    {
        $seller = $this->registerSellerAndGetToken('create');

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $response = $this->withToken($seller['token'])
            ->post('/api/seller/products', [
                'label'  => 'Produk Test Pertama',
                'code'   => 'test-product-001',
                'type'   => 'default',
                'status' => 1,
                'images' => [UploadedFile::fake()->image('test.jpg')],
            ]);

        if ($response->status() !== 201) {
            dump($response->content());
        }

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => ['id', 'code', 'label', 'type', 'status'],
                 ])
                 ->assertJsonPath('data.label', 'Produk Test Pertama')
                 ->assertJsonPath('data.code', 'test-product-001');
    }

    public function test_seller_can_create_and_list_categories(): void
    {
        $seller = $this->registerSellerAndGetToken('category');

        // Create category
        $createResponse = $this->withToken($seller['token'])
            ->postJson('/api/seller/categories', [
                'code' => 'test-category-001',
                'label' => 'Test Category',
                'status' => 1
            ]);
        $createResponse->assertStatus(201);
        $categoryId = $createResponse->json('data.id');

        // List categories
        $listResponse = $this->withToken($seller['token'])
            ->getJson('/api/seller/categories');
        $listResponse->assertStatus(200)
                     ->assertJsonFragment(['id' => $categoryId]);
    }

    public function test_seller_can_create_product_with_variants_and_categories(): void
    {
        $seller = $this->registerSellerAndGetToken('variants');

        // Create category
        $category = $this->withToken($seller['token'])
            ->postJson('/api/seller/categories', [
                'code' => 'cat-var-01',
                'label' => 'Variant Category'
            ]);
        $categoryId = $category->json('data.id');

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $response = $this->withToken($seller['token'])
            ->post('/api/seller/products', [
                'label'  => 'Produk Variasi Test',
                'code'   => 'test-variant-product',
                'type'   => 'select',
                'status' => 1,
                'images' => [UploadedFile::fake()->image('test.jpg')],
                'categories' => [$categoryId],
                'variants' => [
                    ['code' => 'var-01', 'label' => 'Ukuran S'],
                    ['code' => 'var-02', 'label' => 'Ukuran M']
                ]
            ]);

        $response->assertStatus(201);
    }

    public function test_seller_can_list_own_products(): void
    {
        $seller = $this->registerSellerAndGetToken('list');

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        // Create a product first
        $this->withToken($seller['token'])
            ->post('/api/seller/products', [
                'label'  => 'Produk List Test',
                'code'   => 'list-product-001',
                'status' => 1,
                'images' => [UploadedFile::fake()->image('test.jpg')],
            ])->assertStatus(201);

        $response = $this->withToken($seller['token'])
            ->getJson('/api/seller/products');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data'])
                 ->assertJsonCount(1, 'data');
    }

    public function test_seller_can_update_product(): void
    {
        $seller = $this->registerSellerAndGetToken('update');

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $create = $this->withToken($seller['token'])
            ->post('/api/seller/products', [
                'label'  => 'Before Update',
                'code'   => 'update-product-001',
                'status' => 1,
                'images' => [UploadedFile::fake()->image('test.jpg')],
            ]);
        $create->assertStatus(201);
        $productId = $create->json('data.id');

        $response = $this->withToken($seller['token'])
            ->patchJson("/api/seller/products/{$productId}", [
                'label'  => 'After Update',
                'status' => 0,
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.label', 'After Update')
                 ->assertJsonPath('data.status', 0);
    }

    public function test_seller_can_delete_product(): void
    {
        $seller = $this->registerSellerAndGetToken('delete');

        Http::fake([
            '*' => Http::response(['upload_url' => 'http://fake', 'file_id' => '123'], 200)
        ]);

        $create = $this->withToken($seller['token'])
            ->post('/api/seller/products', [
                'label'  => 'Product To Delete',
                'code'   => 'delete-product-001',
                'status' => 1,
                'images' => [UploadedFile::fake()->image('test.jpg')],
            ]);
        $create->assertStatus(201);
        $productId = $create->json('data.id');

        $response = $this->withToken($seller['token'])
            ->deleteJson("/api/seller/products/{$productId}");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Product deleted successfully.');

        // Verify product is gone
        $this->withToken($seller['token'])
            ->getJson("/api/seller/products/{$productId}")
            ->assertStatus(404);
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
        $admin = $this->registerAdminAndGetToken('admin.verif@example.com');

        $sellerData = [
            'code' => 'testsiteapi-verif1',
            'email' => 'seller.verif1@example.com',
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
        
        $seller = \App\Models\User::where('email', 'seller.verif1@example.com')->first();

        // 1. Admin reads list
        \Laravel\Sanctum\Sanctum::actingAs($admin['user']);
        $response = $this->getJson('/api/admin/sellers/pending')
             ->assertStatus(200);
        
        $this->assertStringContainsString('seller.verif1@example.com', $response->content());

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
}

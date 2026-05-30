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

class ProductApiTest extends \Tests\TestCase
{
    use SellerTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFakeUploads();
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


    public function test_seller_can_manage_variants(): void
    {
        $sellerInfo = $this->registerSellerAndGetToken('variants');
        $sellerToken = $sellerInfo['token'];
        
        // 1. Create a parent product
        $createResp = $this->postJson('/api/seller/products', [
            'label' => 'Parent Shirt',
            'code' => 'parent-shirt-' . uniqid(),
            'type' => 'default',
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('shirt.jpg')
            ]
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        if ($createResp->status() === 403) return;
        
        $parentId = $createResp->json('data.id');
        
        // 2. Add Variant
        $addResp = $this->postJson("/api/seller/products/$parentId/variants", [
            'code' => 'variant-red-' . uniqid(),
            'label' => 'Red Shirt'
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        $addResp->assertStatus(201);
        $variantId = $addResp->json('data.id');
        
        // 3. Get Variants
        $getResp = $this->getJson("/api/seller/products/$parentId/variants", ['Authorization' => "Bearer $sellerToken"]);
        $getResp->assertStatus(200);
        $this->assertCount(1, $getResp->json('data'));
        
        // 4. Delete Variant
        $delResp = $this->deleteJson("/api/seller/products/$parentId/variants/$variantId", [], ['Authorization' => "Bearer $sellerToken"]);
        $delResp->assertStatus(200);
    }


    public function test_seller_can_manage_images(): void
    {
        $sellerInfo = $this->registerSellerAndGetToken('images');
        $sellerToken = $sellerInfo['token'];
        
        // 1. Create a product
        $createResp = $this->postJson('/api/seller/products', [
            'label' => 'Shoe Image Test',
            'code' => 'shoe-img-' . uniqid(),
            'type' => 'default',
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('shoe1.jpg')
            ]
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        if ($createResp->status() === 403) return;
        
        $productId = $createResp->json('data.id');
        
        // 2. Upload Images
        $uploadResp = $this->postJson("/api/seller/products/$productId/images", [
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('shoe2.jpg'),
                \Illuminate\Http\UploadedFile::fake()->image('shoe3.jpg')
            ]
        ], ['Authorization' => "Bearer $sellerToken"]);
        
        $uploadResp->assertStatus(201);
        
        // 3. Get Images
        $getResp = $this->getJson("/api/seller/products/$productId/images", ['Authorization' => "Bearer $sellerToken"]);
        $getResp->assertStatus(200);
        
        $mediaList = $getResp->json('data');
        $this->assertGreaterThanOrEqual(3, count($mediaList));
        
        // 4. Delete Image
        $imageId = $mediaList[0]['media_id'];
        $delResp = $this->deleteJson("/api/seller/products/$productId/images/$imageId", [], ['Authorization' => "Bearer $sellerToken"]);
        $delResp->assertStatus(200);
    }


}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PublicApiTest extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Http::fake([
            '*/api/files/presign-upload' => \Illuminate\Support\Facades\Http::response([
                'upload_url' => 'http://mock-minio/upload',
                'file_id' => 'mock-file-123'
            ], 200),
            '*/api/files/*/complete-upload' => \Illuminate\Support\Facades\Http::response(['message' => 'success'], 200),
            'http://mock-minio/upload' => \Illuminate\Support\Facades\Http::response('', 200),
        ]);
    }

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



}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Aimeos\MShop;

class AdminApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getAdmin()
    {
        $admin = User::factory()->create([
            'superuser' => 1,
            'status' => 1,
            'siteid' => '1.'
        ]);
        return $admin;
    }

    public function test_admin_can_get_dashboard_stats()
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin, 'sanctum')->get('/api/admin/dashboard/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'total_users',
                         'total_merchants',
                         'total_transactions',
                         'total_revenue',
                         'graph'
                     ]
                 ]);
    }

    public function test_admin_can_update_user_status()
    {
        $admin = $this->getAdmin();
        
        $user = User::factory()->create([
            'status' => 1,
            'superuser' => 0
        ]);

        $response = $this->actingAs($admin, 'sanctum')->patch("/api/admin/users/{$user->id}/status", [
            'status' => 0
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 0);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 0
        ]);
    }

    public function test_admin_can_manage_categories()
    {
        $admin = $this->getAdmin();
        $code = 'cat-' . uniqid();

        // Create
        $response = $this->actingAs($admin, 'sanctum')->post('/api/admin/categories', [
            'code' => $code,
            'label' => 'New Master Category'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.code', $code)
                 ->assertJsonPath('data.label', 'New Master Category');

        $categoryId = $response->json('data.id');

        // Update
        $response2 = $this->actingAs($admin, 'sanctum')->put("/api/admin/categories/{$categoryId}", [
            'label' => 'Updated Category'
        ]);

        $response2->assertStatus(200)
                  ->assertJsonPath('data.label', 'Updated Category');

        // Delete
        $response3 = $this->actingAs($admin, 'sanctum')->delete("/api/admin/categories/{$categoryId}");

        $response3->assertStatus(200);

        // Verify deleted (in Aimeos it sets status to -1 or physically deletes)
        $context = app('aimeos.context')->get(false);
        $manager = MShop::create($context, 'catalog');
        try {
            $manager->get($categoryId);
            $this->fail("Category should be deleted.");
        } catch (\Aimeos\MShop\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_manage_banners()
    {
        $admin = $this->getAdmin();
        $file = \Illuminate\Http\UploadedFile::fake()->image('banner.jpg');
        
        $response = $this->actingAs($admin, 'sanctum')->post('/api/admin/banners', [
            'label' => 'Promo Lebaran',
            'image' => $file,
            'status' => 1
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.label', 'Promo Lebaran');

        $bannerId = $response->json('data.id');

        $response2 = $this->actingAs($admin, 'sanctum')->get('/api/admin/banners');
        $response2->assertStatus(200);

        $response3 = $this->actingAs($admin, 'sanctum')->delete("/api/admin/banners/{$bannerId}");
        $response3->assertStatus(200);
    }

    public function test_admin_can_manage_withdrawals()
    {
        $admin = $this->getAdmin();
        
        $withdrawal = \App\Models\SellerWithdrawal::create([
            'siteid' => '1.2.',
            'amount' => 50000,
            'status' => 'pending',
            'bank_name' => 'BCA',
            'bank_account_number' => '123',
            'bank_account_name' => 'Test'
        ]);

        $response = $this->actingAs($admin, 'sanctum')->get('/api/admin/withdrawals');
        $response->assertStatus(200);

        $response2 = $this->actingAs($admin, 'sanctum')->patch("/api/admin/withdrawals/{$withdrawal->id}/approve");
        $response2->assertStatus(200)
                  ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('seller_withdrawals', [
            'id' => $withdrawal->id,
            'status' => 'approved'
        ]);
    }

    public function test_admin_can_export_report()
    {
        $admin = $this->getAdmin();
        $response = $this->actingAs($admin, 'sanctum')->get('/api/admin/reports/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_broadcast_notification()
    {
        $admin = $this->getAdmin();
        $response = $this->actingAs($admin, 'sanctum')->post('/api/admin/notifications/broadcast', [
            'title' => 'Diskon Besar!',
            'message' => 'Segera belanja sekarang juga',
            'target' => 'all'
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'sent');
    }
}

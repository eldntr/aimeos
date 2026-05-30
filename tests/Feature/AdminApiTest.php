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
}

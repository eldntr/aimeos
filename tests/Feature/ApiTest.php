<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
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
        $data = [
            'code' => 'testsiteapi-seller',
            'email' => 'seller.api@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register/seller', $data);

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
        ]);

        $user = User::where('email', 'seller.api@example.com')->first();
        $this->assertNotNull($user);

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
}


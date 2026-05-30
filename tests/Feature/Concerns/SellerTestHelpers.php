<?php

namespace Tests\Feature\Concerns;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

trait SellerTestHelpers
{
    /**
     * Set up common fake HTTP responses for S3/File uploads
     */
    protected function setUpFakeUploads(): void
    {
        Http::fake([
            '*/api/files/presign-upload' => Http::response([
                'upload_url' => 'http://mock-minio/upload',
                'file_id' => 'mock-file-123'
            ], 200),
            '*/api/files/*/complete-upload' => Http::response(['message' => 'success'], 200),
            'http://mock-minio/upload' => Http::response('', 200),
            '*/internal/files/compress-pending' => Http::response('', 200),
        ]);
    }

    /**
     * Helper to register a seller and get their token, while auto-approving them
     */
    protected function registerSellerAndGetToken(string $suffix = 'prod'): array
    {
        $unique = uniqid();
        $code = 'mock-seller-' . $suffix . '-' . $unique;
        $email = 'seller.' . $suffix . '.' . $unique . '@example.com';

        // Override specific fake if needed for KTP upload in this method
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
            dump('STATUS: ' . $response->status());
            dump('BODY: ' . $response->content());
        }
        $response->assertStatus(201);
        $data = $response->json();

        $user = User::where('email', $email)->first();
        // Auto-approve for product tests
        $user->seller_status = 'approved';
        $user->save();

        if (Auth::check()) {
            Auth::setUser($user);
        }

        return [
            'user'  => $user,
            'token' => $data['access_token'],
            'code'  => $code,
        ];
    }

    protected function registerAdminAndGetToken(string $email): array
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
}

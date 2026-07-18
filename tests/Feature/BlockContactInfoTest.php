<?php

namespace Tests\Feature;

use App\Http\Middleware\BlockContactInfo;
use Illuminate\Http\Request;
use Tests\TestCase;

class BlockContactInfoTest extends TestCase
{
    /**
     * Test that regular messages are allowed.
     */
    public function test_regular_message_is_allowed()
    {
        $middleware = new BlockContactInfo();
        $request = new Request();
        $request->merge(['message' => 'Halo, saya ingin bertanya tentang produk ini. Apakah ready?']);

        $called = false;
        $response = $middleware->handle($request, function ($req) use (&$called) {
            $called = true;
            return response('OK');
        });

        $this->assertTrue($called);
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test that emails are blocked.
     */
    public function test_email_is_blocked()
    {
        $middleware = new BlockContactInfo();
        $request = new Request();
        $request->merge([
            'message' => 'Silakan hubungi saya di email test.user@gmail.com ya.',
            'temporaryMsgId' => 'temp_123'
        ]);

        $called = false;
        $response = $middleware->handle($request, function ($req) use (&$called) {
            $called = true;
            return response('OK');
        });

        $this->assertFalse($called);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('200', $data['status']);
        $this->assertEquals(1, $data['error']['status']);
        $this->assertStringContainsString('informasi kontak', $data['error']['message']);
        $this->assertEquals('temp_123', $data['tempID']);
    }

    /**
     * Test that phone numbers are blocked.
     */
    public function test_phone_number_is_blocked()
    {
        $middleware = new BlockContactInfo();

        $phoneNumbers = [
            'Hubungi WA saya 081234567890',
            'No hp saya: +62 812-3456-789',
            'Silakan sms ke 021-1234567',
            '6287712345678',
            '0812 3456 7890'
        ];

        foreach ($phoneNumbers as $msg) {
            $request = new Request();
            $request->merge([
                'message' => $msg,
                'temporaryMsgId' => 'temp_123'
            ]);

            $called = false;
            $response = $middleware->handle($request, function ($req) use (&$called) {
                $called = true;
                return response('OK');
            });

            $this->assertFalse($called, "Phone number format should be blocked: {$msg}");
            $data = json_decode($response->getContent(), true);
            $this->assertEquals(1, $data['error']['status']);
        }
    }

    /**
     * Test that physical addresses are blocked.
     */
    public function test_address_is_blocked()
    {
        $middleware = new BlockContactInfo();

        $addresses = [
            'Alamat saya di Jl. Sudirman No 123',
            'Kirim saja ke Jalan Raya Merdeka Blok B',
            'Rumah saya di Perumahan Indah Kapuk',
            'Kecamatan Klojen, Kelurahan Oro-oro Dowo',
            'Kode pos rumah saya 65119'
        ];

        foreach ($addresses as $msg) {
            $request = new Request();
            $request->merge([
                'message' => $msg,
                'temporaryMsgId' => 'temp_123'
            ]);

            $called = false;
            $response = $middleware->handle($request, function ($req) use (&$called) {
                $called = true;
                return response('OK');
            });

            $this->assertFalse($called, "Address format should be blocked: {$msg}");
            $data = json_decode($response->getContent(), true);
            $this->assertEquals(1, $data['error']['status']);
        }
    }

    /**
     * Test that user is temporarily muted after sending blocked content.
     */
    public function test_user_temporary_mute_block()
    {
        // 1. Mock authentication
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $middleware = new BlockContactInfo();
        $request = new Request();
        $request->merge([
            'message' => 'Hubungi saya di 081234567890',
            'temporaryMsgId' => 'temp_123'
        ]);

        // First attempt (contains contact details) -> Should set mute timeout
        $called = false;
        $response = $middleware->handle($request, function ($req) use (&$called) {
            $called = true;
            return response('OK');
        });

        $this->assertFalse($called);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('chat_timeout_' . $user->id));

        // Second attempt (regular safe message inside mute window) -> Should be blocked due to timeout!
        $request2 = new Request();
        $request2->merge([
            'message' => 'Halo apakah ready?',
            'temporaryMsgId' => 'temp_456'
        ]);

        $called2 = false;
        $response2 = $middleware->handle($request2, function ($req) use (&$called2) {
            $called2 = true;
            return response('OK');
        });

        $this->assertFalse($called2);
        $data = json_decode($response2->getContent(), true);
        $this->assertStringContainsString('diblokir sementara', $data['error']['message']);

        // Clean up
        \Illuminate\Support\Facades\Cache::forget('chat_timeout_' . $user->id);
        $user->delete();
    }
}

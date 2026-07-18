<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class BlockContactInfo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = Auth::id();

        // 1. Check if user is currently in a temporary timeout/mute
        if ($userId && Cache::has('chat_timeout_' . $userId)) {
            $timeoutUntil = Cache::get('chat_timeout_' . $userId);
            $secondsLeft = $timeoutUntil->timestamp - time();

            if ($secondsLeft > 0) {
                return response()->json([
                    'status' => '200',
                    'error' => (object)[
                        'status' => 1,
                        'message' => "Anda diblokir sementara karena mengirim pesan melanggar. Silakan coba lagi dalam {$secondsLeft} detik."
                    ],
                    'error_msg' => "Anda diblokir sementara karena mengirim pesan melanggar. Silakan coba lagi dalam {$secondsLeft} detik.",
                    'tempID' => $request->input('temporaryMsgId'),
                ]);
            }
        }

        // 2. Scan incoming message for contact details
        if ($request->has('message') && !empty($request->input('message'))) {
            $message = $request->input('message');

            $blockedType = $this->detectContactInfo($message);

            if ($blockedType) {
                Log::warning("Message blocked: Contains {$blockedType} info. Message: " . substr($message, 0, 50));

                // Apply a 60-second mute timeout on the user
                if ($userId) {
                    Cache::put('chat_timeout_' . $userId, now()->addSeconds(60), 60);
                }

                return response()->json([
                    'status' => '200',
                    'error' => (object)[
                        'status' => 1,
                        'message' => 'Pesan tidak dapat dikirim karena mengandung informasi kontak (No. HP, Email, atau Alamat).'
                    ],
                    'error_msg' => 'Pesan tidak dapat dikirim karena mengandung informasi kontak (No. HP, Email, atau Alamat).',
                    'tempID' => $request->input('temporaryMsgId'),
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Detect if the message contains contact info (email, phone, address).
     *
     * @param string $text
     * @return string|null
     */
    private function detectContactInfo(string $text): ?string
    {
        // 1. Email detection
        // Matches typical email pattern e.g. hello@example.com
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            return 'email';
        }

        // 2. Phone number detection
        // Matches Indonesian formats starting with +62, 62, or 0, followed by 7-12 digits with optional separators (space, dash, dot, brackets)
        // E.g., 081234567890, +62 812-3456-7890, 021-1234567
        if (preg_match('/(?:\+?62|0)\s*(?:\d\s*[\-\.\(\)]?\s*){7,11}\d/', $text)) {
            return 'phone';
        }

        // 3. Address detection
        // RT/RW pattern (e.g., RT 03/RW 04, RT03/RW04)
        if (preg_match('/\brt\s*\d+\s*[\/\\\]?\s*rw\s*\d+/i', $text)) {
            return 'address';
        }

        // Jalan / Jl / Jln followed by street name or details
        if (preg_match('/\b(jl|jln|jalan)\.?\s+[a-zA-Z0-9\s]{3,}/i', $text)) {
            return 'address';
        }

        // Location keywords (e.g. Kecamatan X, Kelurahan Y, Kabupaten Z, Perumahan A, Kode Pos 12345)
        if (preg_match('/\b(kecamatan|kelurahan|kabupaten|perumahan|provinsi)\s+[a-zA-Z\s]{3,}/i', $text)) {
            return 'address';
        }
        if (preg_match('/\b(kode\s*pos|kodepos)\b.{0,25}\b\d{5}\b/i', $text)) {
            return 'address';
        }

        return null;
    }
}

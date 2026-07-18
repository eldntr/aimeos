<?php

namespace App\Http\Middleware;

use App\Models\ChatBlockedKeyword;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockContactInfo
{
    /**
     * Handle an incoming request.
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

        // 2. Scan incoming message
        if ($request->has('message') && !empty($request->input('message'))) {
            $message = $request->input('message');

            // 2a. Check regex-based contact info (phone, email, address)
            $blockedType = $this->detectContactInfo($message);
            if ($blockedType) {
                Log::warning("Message blocked: Contains {$blockedType} info. Message: " . substr($message, 0, 50));
                $this->applyMuteTimeout($userId);

                return $this->blockedResponse(
                    'Pesan tidak dapat dikirim karena mengandung informasi kontak (No. HP, Email, atau Alamat).',
                    $request->input('temporaryMsgId')
                );
            }

            // 2b. Check admin-managed blocked keywords from DB (cached)
            $matchedKeyword = $this->detectBlockedKeyword($message);
            if ($matchedKeyword) {
                Log::warning("Message blocked: Contains banned keyword '{$matchedKeyword}'. Message: " . substr($message, 0, 50));
                $this->applyMuteTimeout($userId);

                return $this->blockedResponse(
                    "Pesan tidak dapat dikirim karena mengandung kata yang tidak diizinkan: \"{$matchedKeyword}\".",
                    $request->input('temporaryMsgId')
                );
            }
        }

        return $next($request);
    }

    /**
     * Apply a 60-second mute/timeout on the user.
     */
    private function applyMuteTimeout(?int $userId): void
    {
        if ($userId) {
            Cache::put('chat_timeout_' . $userId, now()->addSeconds(60), 60);
        }
    }

    /**
     * Build a blocked response JSON.
     */
    private function blockedResponse(string $message, ?string $tempId = null)
    {
        return response()->json([
            'status' => '200',
            'error' => (object)[
                'status' => 1,
                'message' => $message,
            ],
            'error_msg' => $message,
            'tempID' => $tempId,
        ]);
    }

    /**
     * Check message against admin-managed blocked keywords (DB, cached 5 min).
     */
    private function detectBlockedKeyword(string $text): ?string
    {
        try {
            $keywords = ChatBlockedKeyword::getActiveKeywords();
            $lowerText = mb_strtolower($text);

            foreach ($keywords as $keyword) {
                if (str_contains($lowerText, $keyword)) {
                    return $keyword;
                }
            }
        } catch (\Exception $e) {
            // If DB is unavailable, skip this check gracefully
            Log::error('BlockContactInfo: Failed to load blocked keywords from DB: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Detect if the message contains contact info (email, phone, address).
     */
    private function detectContactInfo(string $text): ?string
    {
        // 1. Email detection
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            return 'email';
        }

        // 2. Phone number detection (Indonesian formats)
        if (preg_match('/(?:\+?62|0)\s*(?:\d\s*[\-\.\(\)]?\s*){7,11}\d/', $text)) {
            return 'phone';
        }

        // 3. Address detection: RT/RW pattern
        if (preg_match('/\brt\s*\d+\s*[\/\\\\]?\s*rw\s*\d+/i', $text)) {
            return 'address';
        }

        // Jalan / Jl / Jln
        if (preg_match('/\b(jl|jln|jalan)\.?\s+[a-zA-Z0-9\s]{3,}/i', $text)) {
            return 'address';
        }

        // Location keywords
        if (preg_match('/\b(kecamatan|kelurahan|kabupaten|perumahan|provinsi)\s+[a-zA-Z\s]{3,}/i', $text)) {
            return 'address';
        }
        if (preg_match('/\b(kode\s*pos|kodepos)\b.{0,25}\b\d{5}\b/i', $text)) {
            return 'address';
        }

        return null;
    }
}

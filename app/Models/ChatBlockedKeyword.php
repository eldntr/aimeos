<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class ChatBlockedKeyword
 *
 * Handles chat blocked keyword operations for the application.
 */
class ChatBlockedKeyword extends Model
{
    protected $fillable = ['keyword', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active blocked keywords, cached for 5 minutes.
     */
    public static function getActiveKeywords(): array
    {
        return Cache::remember('chat_blocked_keywords', 300, function () {
            return static::where('is_active', true)
                ->pluck('keyword')
                ->map(fn($k) => mb_strtolower(trim($k)))
                ->toArray();
        });
    }

    /**
     * Clear the keyword cache when keywords are modified.
     */
    public static function clearCache(): void
    {
        Cache::forget('chat_blocked_keywords');
    }

    protected static function booted(): void
    {
        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }
}

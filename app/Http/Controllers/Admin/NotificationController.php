<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class NotificationController
 *
 * Handles notification controller operations for the application.
 */
class NotificationController extends Controller
{
    /**
     * Broadcast.
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'sometimes|in:all,customers,sellers'
        ]);

        $target = $request->input('target', 'all');

        // Logic to send push notification via FCM / OneSignal would go here.
        // For now, we simulate success by logging it.
        Log::info("Broadcasting push notification to {$target}", [
            'title' => $request->title,
            'message' => $request->message
        ]);

        return response()->json([
            'message' => 'Push notification berhasil di-broadcast ke target.',
            'data' => [
                'title' => $request->title,
                'target' => $target,
                'status' => 'sent'
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class NotificationController
 *
 * Handles notification controller operations for the application.
 */
class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure the User model uses the Notifiable trait (default in Laravel)
        if (!method_exists($user, 'notifications')) {
            return response()->json(['message' => 'Sistem notifikasi tidak didukung untuk pengguna ini.'], 500);
        }

        $perPage = $request->query('per_page', 20);
        
        $notifications = $user->notifications()->paginate($perPage);

        // Format for cleaner output
        $formatted = $notifications->through(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json([
            'message' => 'Riwayat notifikasi berhasil diambil.',
            'data' => $formatted,
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        if (!method_exists($user, 'notifications')) {
            return response()->json(['message' => 'Sistem notifikasi tidak didukung untuk pengguna ini.'], 500);
        }

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notifikasi telah ditandai sebagai dibaca.',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at
            ],
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }
}

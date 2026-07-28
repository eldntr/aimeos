<?php

namespace App\Http\Controllers;

use App\Models\ChMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class MarketplaceChatController
 *
 * Handles marketplace chat controller operations for the application.
 */
class MarketplaceChatController extends Controller
{
    /**
     * Get all messages between the authenticated user and target user.
     * Accepts either ?shop=shop_code or ?user_id=id (internal fallback).
     */
    public function getMessages(Request $request)
    {
        $authId = Auth::id();
        $targetId = null;
        $shopCode = null;

        // Prefer shop_code for privacy; fallback to user_id for internal calls
        if ($request->query('shop')) {
            $shopCode = $request->query('shop');
            $targetId = $this->resolveUserIdFromShopCode($shopCode);

            if (!$targetId) {
                return response()->json(['message' => 'Toko tidak ditemukan.'], 404);
            }
        } else {
            $targetId = $request->query('user_id');
        }

        if (!$targetId) {
            return response()->json(['message' => 'User ID or shop code is required.'], 400);
        }

        $targetUser = User::find($targetId);
        if (!$targetUser) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $messages = ChMessage::where(function ($q) use ($authId, $targetId) {
            $q->where('from_id', $authId)->where('to_id', $targetId);
        })->orWhere(function ($q) use ($authId, $targetId) {
            $q->where('from_id', $targetId)->where('to_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        // Mark these messages as read
        ChMessage::where('from_id', $targetId)
            ->where('to_id', $authId)
            ->where('seen', 0)
            ->update(['seen' => 1]);

        $formattedMessages = $messages->map(function ($msg) {
            $createdAt = $msg->created_at->timezone(config('app.timezone'));

            return [
                'id'        => $msg->id,
                'from_id'   => $msg->from_id,
                'to_id'     => $msg->to_id,
                'body'      => $msg->body,
                'seen'      => $msg->seen,
                'time'      => $createdAt->format('H:i'),
                'date'      => $createdAt->format('d M Y'),
                'is_sender' => $msg->from_id == Auth::id(),
            ];
        });

        // Determine the shop_code for this target user (to avoid exposing user_id in responses)
        $resolvedShopCode = $shopCode ?? $this->resolveShopCodeFromUserId($targetId);

        return response()->json([
            'target_user' => [
                'id'        => $targetUser->id,
                'shop_code' => $resolvedShopCode,
                'name'      => $targetUser->name,
                'avatar'    => $this->getAvatarUrl($targetUser->avatar),
            ],
            'messages' => $formattedMessages
        ]);
    }

    /**
     * Send a new message.
     * Accepts either user_id or shop_code for the recipient.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'user_id'   => 'required_without:shop_code',
            'shop_code' => 'required_without:user_id',
            'message'   => 'required|string',
        ]);

        $authId = Auth::id();
        $body = $request->input('message');

        if ($request->filled('shop_code')) {
            $targetId = $this->resolveUserIdFromShopCode($request->input('shop_code'));
            if (!$targetId) {
                return response()->json(['message' => 'Toko tidak ditemukan.'], 404);
            }
        } else {
            $targetId = $request->input('user_id');
        }

        $targetUser = User::find($targetId);
        if (!$targetUser) {
            return response()->json(['message' => 'Recipient user not found.'], 404);
        }

        $msg = new ChMessage();
        $msg->id      = (string) Str::uuid();
        $msg->from_id = $authId;
        $msg->to_id   = $targetId;
        $msg->body    = htmlentities(trim($body), ENT_QUOTES, 'UTF-8');
        $msg->seen    = 0;
        $msg->save();

        return response()->json([
            'status'  => '200',
            'message' => [
                'id'        => $msg->id,
                'from_id'   => $msg->from_id,
                'to_id'     => $msg->to_id,
                'body'      => $msg->body,
                'time'      => $msg->created_at->timezone(config('app.timezone'))->format('H:i'),
                'is_sender' => true,
            ]
        ]);
    }

    /**
     * Get a list of conversations for the current user.
     */
    public function getConversations()
    {
        $authId = Auth::id();

        // Fetch user IDs of all contacts who have sent or received messages
        $sentIds = ChMessage::where('from_id', $authId)->pluck('to_id')->toArray();
        $receivedIds = ChMessage::where('to_id', $authId)->pluck('from_id')->toArray();
        $userIds = array_unique(array_merge($sentIds, $receivedIds));
        
        // Exclude self if present
        $userIds = array_values(array_filter($userIds, fn($id) => $id != $authId));

        $conversations = [];

        foreach ($userIds as $id) {
            $user = User::find($id);
            if (!$user) {
                continue;
            }

            // Get last message
            $lastMessage = ChMessage::where(function ($q) use ($authId, $id) {
                $q->where('from_id', $authId)->where('to_id', $id);
            })->orWhere(function ($q) use ($authId, $id) {
                $q->where('from_id', $id)->where('to_id', $authId);
            })->orderBy('created_at', 'desc')->first();

            if (!$lastMessage) {
                continue;
            }

            // Get unread count
            $unreadCount = ChMessage::where('from_id', $id)
                ->where('to_id', $authId)
                ->where('seen', 0)
                ->count();

            $conversations[] = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $this->getAvatarUrl($user->avatar),
                ],
                'last_message' => [
                    'body' => html_entity_decode($lastMessage->body),
                    'time' => $lastMessage->created_at->timezone(config('app.timezone'))->diffForHumans(null, true, true), // Short diff
                    'timestamp' => $lastMessage->created_at->timestamp,
                    'is_sender' => $lastMessage->from_id == $authId,
                ],
                'unread_count' => $unreadCount,
            ];
        }

        // Sort conversations by last message timestamp descending
        usort($conversations, function ($a, $b) {
            return $b['last_message']['timestamp'] <=> $a['last_message']['timestamp'];
        });

        return response()->json($conversations);
    }

    /**
     * Mark all messages from a user as read.
     */
    public function markAsRead(Request $request)
    {
        $request->validate(['user_id' => 'required']);
        $authId = Auth::id();
        $targetId = $request->input('user_id');

        ChMessage::where('from_id', $targetId)
            ->where('to_id', $authId)
            ->where('seen', 0)
            ->update(['seen' => 1]);

        return response()->json(['message' => 'Messages marked as read.']);
    }

    /**
     * Resolve a shop_code to its seller user_id.
     * Looks up locale/site by code, then finds the user with that site_id.
     */
    private function resolveUserIdFromShopCode(string $shopCode): ?int
    {
        $site = DB::table('mshop_locale_site')
            ->where('code', $shopCode)
            ->where('status', 1)
            ->first();

        if (!$site) {
            return null;
        }

        $user = DB::table('users')
            ->where('siteid', $site->id)
            ->first();

        return $user?->id;
    }

    /**
     * Resolve a user_id to their shop_code (site code).
     */
    private function resolveShopCodeFromUserId(int $userId): ?string
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user || !$user->siteid) {
            return null;
        }

        $site = DB::table('mshop_locale_site')
            ->where('id', $user->siteid)
            ->first();

        return $site?->code;
    }

    /**
     * Get user avatar URL.
     */
    private function getAvatarUrl($avatar)
    {
        if (!$avatar || $avatar === config('chatify.user_avatar.fallback', 'avatar.png')) {
            return asset('css/chatify/images/avatar.png');
        }
        return asset('storage/' . config('chatify.user_avatar.folder', 'users-avatar') . '/' . $avatar);
    }
}

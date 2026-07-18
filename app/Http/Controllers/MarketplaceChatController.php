<?php

namespace App\Http\Controllers;

use App\Models\ChMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceChatController extends Controller
{
    /**
     * Get all messages between the authenticated user and target user.
     */
    public function getMessages(Request $request)
    {
        $authId = Auth::id();
        $targetId = $request->query('user_id');

        if (!$targetId) {
            return response()->json(['message' => 'User ID is required.'], 400);
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
            return [
                'id' => $msg->id,
                'from_id' => $msg->from_id,
                'to_id' => $msg->to_id,
                'body' => $msg->body,
                'seen' => $msg->seen,
                'time' => $msg->created_at->format('H:i'),
                'date' => $msg->created_at->format('d M Y'),
                'is_sender' => $msg->from_id == Auth::id(),
            ];
        });

        return response()->json([
            'target_user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'avatar' => $this->getAvatarUrl($targetUser->avatar),
            ],
            'messages' => $formattedMessages
        ]);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'message' => 'required|string',
        ]);

        $authId = Auth::id();
        $targetId = $request->input('user_id');
        $body = $request->input('message');

        $targetUser = User::find($targetId);
        if (!$targetUser) {
            return response()->json(['message' => 'Recipient user not found.'], 404);
        }

        $msg = new ChMessage();
        $msg->id = (string) Str::uuid();
        $msg->from_id = $authId;
        $msg->to_id = $targetId;
        $msg->body = htmlentities(trim($body), ENT_QUOTES, 'UTF-8');
        $msg->seen = 0;
        $msg->save();

        return response()->json([
            'status' => '200',
            'message' => [
                'id' => $msg->id,
                'from_id' => $msg->from_id,
                'to_id' => $msg->to_id,
                'body' => $msg->body,
                'time' => $msg->created_at->format('H:i'),
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
                    'time' => $lastMessage->created_at->diffForHumans(null, true, true), // Short diff
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

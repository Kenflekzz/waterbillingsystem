<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class UserNotificationController extends Controller
{
    public function getNotifications()
    {
        try {
            $user = Auth::guard('user')->user();

            if (!$user) {
                return response()->json([
                    'notifications' => [],
                    'error' => 'Not authenticated'
                ], 401);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notification) {
                    $notification->data = json_decode($notification->data, true);
                    return $notification;
                });

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $notifications->where('is_read', false)->count()
            ]);

        } catch (Exception $e) {
            Log::error("Notification error: " . $e->getMessage());

            return response()->json([
                'notifications' => [],
                'error' => 'Server error'
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $user = Auth::guard('user')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            $notification->update([
                'is_read' => true,
                'read_at' => now(), // Now this works!
            ]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error("Mark as read error: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $user = Auth::guard('user')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(), // Now this works!
                ]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error("Mark all as read error: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function getUnreadCount()
    {
        try {
            $user = Auth::guard('user')->user();

            if (!$user) {
                return response()->json(['count' => 0], 401);
            }

            $count = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json(['count' => $count]);

        } catch (Exception $e) {
            Log::error("Unread count error: " . $e->getMessage());
            return response()->json(['count' => 0], 500);
        }
    }
}
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Logs;

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
                ->get();

            return response()->json([
                'notifications' => $notifications
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
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['success' => true]);
    }
}


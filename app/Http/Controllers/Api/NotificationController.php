<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\CustomerActivityNotification;

class NotificationController extends Controller
{
    /**
     * Fetch all notifications for the authenticated customer.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()->latest()->take(50)->get()->map(function ($n) {
            $data = is_array($n->data) ? $n->data : json_decode($n->data, true);
            return [
                'id'           => (string) $n->id,
                'title'        => $data['title'] ?? 'Notification',
                'body'         => $data['body'] ?? '',
                'type'         => $data['type'] ?? 'system',
                'reference_id' => $data['reference_id'] ?? null,
                'action_route' => $data['action_route'] ?? null,
                'is_read'      => !is_null($n->read_at),
                'read_at'      => $n->read_at ? $n->read_at->toIso8601String() : null,
                'created_at'   => $n->created_at ? $n->created_at->toIso8601String() : now()->toIso8601String(),
                'metadata'     => $data['metadata'] ?? null,
            ];
        });

        return response()->json([
            'status'       => true,
            'message'      => 'Notifications retrieved successfully',
            'unread_count' => $unreadCount,
            'data'         => $notifications,
        ], 200);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id, Request $request)
    {
        $user = $request->user();
        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Notification marked as read',
        ], 200);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'status'  => true,
            'message' => 'All notifications marked as read',
        ], 200);
    }

    /**
     * Delete a single notification.
     */
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->delete();
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Notification deleted successfully',
        ], 200);
    }

    /**
     * Delete all notifications for the user.
     */
    public function clearAll(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->notifications()->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'All notifications cleared successfully',
        ], 200);
    }
}

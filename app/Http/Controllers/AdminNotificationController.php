<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    /**
     * Fetch unread notifications count and latest 10 notifications via AJAX.
     */
    public function fetchNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $unreadCount = $user->unreadNotifications->count();
        $notifications = $user->notifications()->take(10)->get()->map(function ($n) {
            $data = $n->data;
            $isAccepted = ($data['status'] ?? '') === 'accepted';
            $driverName = $data['driver_name'] ?? 'Driver';
            $shipmentTitle = $data['shipment_title'] ?? ('Shipment #' . ($data['shipment_id'] ?? ''));
            $titleEn = $isAccepted ? 'Invitation Accepted' : 'Invitation Declined';
            $messageEn = $isAccepted 
                ? "Driver {$driverName} accepted invitation for {$shipmentTitle}." 
                : "Driver {$driverName} declined invitation for {$shipmentTitle}.";

            return [
                'id'               => $n->id,
                'status'           => $data['status'] ?? ($isAccepted ? 'accepted' : 'declined'),
                'is_accepted'      => $isAccepted,
                'read_at'          => $n->read_at,
                'is_read'          => !is_null($n->read_at),
                'created_at'       => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                'title_en'         => $titleEn,
                'title_ar'         => $titleEn,
                'message'          => $messageEn,
                'driver_name'      => $driverName,
                'shipment_title'   => $shipmentTitle,
                'rejection_reason' => $data['rejection_reason'] ?? null,
                'target_url'       => route('admin.notification.read', $n->id, false),
            ];
        });

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read and redirect to target URL.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
                $targetUrl = route('all.shipment.invitations', [], false);
                return redirect($targetUrl);
            }
        }
        return redirect()->route('all.shipment.invitations', [], false);
    }

    /**
     * Mark all unread notifications as read via AJAX or POST.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'All notifications marked as read.'
        ]);
    }

    /**
     * Delete a single notification by ID.
     */
    public function deleteNotification($id)
    {
        $user = Auth::user();
        if ($user) {
            $user->notifications()->where('id', $id)->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification deleted successfully.'
        ]);
    }

    /**
     * Clear all notifications for the user.
     */
    public function clearAllNotifications()
    {
        $user = Auth::user();
        if ($user) {
            $user->notifications()->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'All notifications deleted successfully.'
        ]);
    }
}

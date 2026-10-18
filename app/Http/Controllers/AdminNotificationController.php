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
            $data = $n->data ?? [];
            $type = $data['type'] ?? 'system';

            // Check if it's a driver invitation
            if (isset($data['driver_name']) || isset($data['invitation_id'])) {
                $isAccepted = ($data['status'] ?? '') === 'accepted';
                $driverName = $data['driver_name'] ?? 'Driver';
                $shipmentTitle = $data['shipment_title'] ?? ('Shipment #' . ($data['shipment_id'] ?? ''));
                $titleEn = $isAccepted ? 'Invitation Accepted' : 'Invitation Declined';
                $messageEn = $isAccepted 
                    ? "Driver {$driverName} accepted invitation for {$shipmentTitle}." 
                    : "Driver {$driverName} declined invitation for {$shipmentTitle}.";
                $iconClass = $isAccepted ? 'bx-check-shield' : 'bx-x-circle';
                $iconColor = $isAccepted ? '#10B981' : '#F43F5E';
                $iconBg = $isAccepted ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)';
                $iconBorder = $isAccepted ? 'rgba(16, 185, 129, 0.3)' : 'rgba(244, 63, 94, 0.3)';
                $titleColorClass = $isAccepted ? 'notif-item-title-success' : 'notif-item-title-danger';
            } else {
                $titleEn = $data['title_en'] ?? ($data['title'] ?? 'Notification');
                $messageEn = $data['message'] ?? ($data['body'] ?? '');
                $iconClass = $data['icon'] ?? 'bx-bell';
                $iconColor = $data['icon_color'] ?? '#0284C7';
                $iconBg = $data['icon_bg'] ?? 'rgba(2, 132, 199, 0.15)';
                $iconBorder = 'rgba(2, 132, 199, 0.3)';
                $titleColorClass = ($data['status'] ?? '') === 'success' ? 'notif-item-title-success' : '';
            }

            return [
                'id'                => $n->id,
                'type'              => $type,
                'status'            => $data['status'] ?? 'info',
                'is_accepted'       => ($data['status'] ?? '') === 'accepted',
                'read_at'           => $n->read_at,
                'is_read'           => !is_null($n->read_at),
                'created_at'        => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                'title_en'          => $titleEn,
                'title_ar'          => $data['title_ar'] ?? $titleEn,
                'message'           => $messageEn,
                'icon_class'        => $iconClass,
                'icon_color'        => $iconColor,
                'icon_bg'           => $iconBg,
                'icon_border'       => $iconBorder,
                'title_color_class' => $titleColorClass,
                'rejection_reason'  => $data['rejection_reason'] ?? null,
                'target_url'        => route('admin.notification.read', $n->id, false),
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
                $targetUrl = $notification->data['target_url'] ?? null;
                if ($targetUrl) {
                    return redirect($targetUrl);
                }
                return redirect()->route('dashboard');
            }
        }
        return redirect()->route('dashboard');
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

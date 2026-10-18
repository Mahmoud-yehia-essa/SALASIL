<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Send notification to all admin users.
     */
    public static function send(array $payload)
    {
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AdminAlertNotification($payload));
            }
        } catch (\Throwable $e) {
            Log::error('AdminNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Notify admins when a new member registers (Client or Driver).
     */
    public static function newMemberRegistered(User $user)
    {
        $roleName = ucfirst(str_replace('_', ' ', $user->role ?? 'member'));
        $fullName = trim(($user->fname ?? '') . ' ' . ($user->lname ?? ''));
        if (empty($fullName)) {
            $fullName = $user->username ?? $user->email ?? 'User';
        }

        self::send([
            'type'        => 'new_user',
            'title'       => "New {$roleName} Registered",
            'title_en'    => "New {$roleName} Registered",
            'message'     => "{$fullName} ({$user->email}) has registered on Salasil.",
            'target_url'  => route('all.owners', [], false),
            'icon'        => 'bx-user-plus',
            'icon_bg'     => 'rgba(16, 185, 129, 0.15)',
            'icon_color'  => '#10B981',
            'status'      => 'success',
            'reference_id'=> $user->id,
            'metadata'    => [
                'user_id' => $user->id,
                'role'    => $user->role,
                'email'   => $user->email,
            ],
        ]);
    }

    /**
     * Notify admins when a new trip order is placed.
     */
    public static function newOrderCreated($order)
    {
        $orderCode = $order->order_number ?? $order->order_code ?? ('#' . $order->id);
        $customerName = 'Customer';
        if ($order->customer) {
            $customerName = trim(($order->customer->fname ?? '') . ' ' . ($order->customer->lname ?? ''));
        } elseif ($order->customer_id) {
            $c = User::find($order->customer_id);
            if ($c) {
                $customerName = trim(($c->fname ?? '') . ' ' . ($c->lname ?? ''));
            }
        }

        $totalPrice = $order->total_price ? number_format((float)$order->total_price, 3) . ' KWD' : '';

        self::send([
            'type'        => 'new_order',
            'title'       => "New Order Placed ({$orderCode})",
            'title_en'    => "New Order Placed ({$orderCode})",
            'message'     => "Order {$orderCode} created by {$customerName}" . ($totalPrice ? " ({$totalPrice})." : "."),
            'target_url'  => route('all.scheduled.trip.orders', [], false),
            'icon'        => 'bx-package',
            'icon_bg'     => 'rgba(2, 132, 199, 0.15)',
            'icon_color'  => '#0284C7',
            'status'      => 'info',
            'reference_id'=> $order->id,
            'metadata'    => [
                'order_id'    => $order->id,
                'order_code'  => $orderCode,
                'customer_id' => $order->customer_id,
                'total_price' => $order->total_price,
            ],
        ]);
    }

    /**
     * Notify admins when a general shipment order is placed.
     */
    public static function newShipmentCreated($shipment)
    {
        $shipmentCode = $shipment->tracking_number ?? ('#' . $shipment->id);
        $customerName = 'Customer';
        if ($shipment->customer) {
            $customerName = trim(($shipment->customer->fname ?? '') . ' ' . ($shipment->customer->lname ?? ''));
        } elseif ($shipment->customer_id) {
            $c = User::find($shipment->customer_id);
            if ($c) {
                $customerName = trim(($c->fname ?? '') . ' ' . ($c->lname ?? ''));
            }
        }

        $price = $shipment->initial_price ? number_format((float)$shipment->initial_price, 3) . ' KWD' : '';

        self::send([
            'type'        => 'new_order',
            'title'       => "New Shipment Order ({$shipmentCode})",
            'title_en'    => "New Shipment Order ({$shipmentCode})",
            'message'     => "Shipment {$shipmentCode} created by {$customerName}" . ($price ? " ({$price})." : "."),
            'target_url'  => route('all.shipments', [], false),
            'icon'        => 'bx-package',
            'icon_bg'     => 'rgba(2, 132, 199, 0.15)',
            'icon_color'  => '#0284C7',
            'status'      => 'info',
            'reference_id'=> $shipment->id,
            'metadata'    => [
                'shipment_id' => $shipment->id,
                'customer_id' => $shipment->customer_id,
                'price'       => $shipment->initial_price,
            ],
        ]);
    }

    /**
     * Notify admins when a new support ticket / inquiry is submitted.
     */
    public static function newSupportTicket($ticket)
    {
        $ticketCode = $ticket->ticket_number ?? ('#' . $ticket->id);
        $subject = $ticket->subject ?? 'Inquiry';
        $sender = $ticket->user_name ?: ($ticket->user ? trim(($ticket->user->fname ?? '') . ' ' . ($ticket->user->lname ?? '')) : 'User');

        self::send([
            'type'        => 'new_inquiry',
            'title'       => "New Support Ticket ({$ticketCode})",
            'title_en'    => "New Support Ticket ({$ticketCode})",
            'message'     => "{$sender}: \"{$subject}\".",
            'target_url'  => route('show.support.ticket', $ticket->id, false),
            'icon'        => 'bx-help-circle',
            'icon_bg'     => 'rgba(139, 92, 246, 0.15)',
            'icon_color'  => '#8B5CF6',
            'status'      => 'purple',
            'reference_id'=> $ticket->id,
            'metadata'    => [
                'ticket_id' => $ticket->id,
                'subject'   => $subject,
            ],
        ]);
    }

    /**
     * Notify admins when a payment is received.
     */
    public static function paymentReceived($payment)
    {
        $txnId = $payment->transaction_id ?? ('#' . $payment->id);
        $amount = number_format((float)($payment->amount ?? 0), 3) . ' KWD';

        self::send([
            'type'        => 'payment_received',
            'title'       => "Payment Received ({$txnId})",
            'title_en'    => "Payment Received ({$txnId})",
            'message'     => "Received payment of {$amount} (Txn: {$txnId}).",
            'target_url'  => route('all.payments', [], false),
            'icon'        => 'bx-credit-card',
            'icon_bg'     => 'rgba(16, 185, 129, 0.15)',
            'icon_color'  => '#10B981',
            'status'      => 'success',
            'reference_id'=> $payment->id,
            'metadata'    => [
                'payment_id' => $payment->id,
                'amount'     => $payment->amount,
            ],
        ]);
    }
}

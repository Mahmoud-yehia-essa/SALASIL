<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ShipmentDriverInvitation;

class DriverInvitationResponded extends Notification
{
    use Queueable;

    public $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(ShipmentDriverInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $driver = $this->invitation->driver;
        $driverName = $driver ? trim(($driver->fname ?? '') . ' ' . ($driver->lname ?? '')) : 'Driver';
        $shipment = $this->invitation->shipment;
        $shipmentTitle = $shipment ? ($shipment->shipment_name ?: 'Shipment #' . $shipment->id) : ('Shipment #' . $this->invitation->shipment_id);

        $isAccepted = $this->invitation->status === 'accepted';
        $titleEn = $isAccepted ? 'Invitation Accepted' : 'Invitation Declined';

        return [
            'invitation_id'    => $this->invitation->id,
            'shipment_id'      => $this->invitation->shipment_id,
            'shipment_title'   => $shipmentTitle,
            'driver_id'        => $this->invitation->driver_id,
            'driver_name'      => $driverName,
            'driver_phone'     => $driver->phone ?? '',
            'status'           => $this->invitation->status, // 'accepted' or 'rejected'
            'rejection_reason' => $this->invitation->rejection_reason,
            'offered_price'    => $this->invitation->offered_price,
            'title_en'         => $titleEn,
            'title_ar'         => $titleEn,
            'message'          => $isAccepted 
                                  ? "Driver {$driverName} accepted invitation for {$shipmentTitle}."
                                  : "Driver {$driverName} declined invitation for {$shipmentTitle}.",
            'target_url'       => route('all.shipment.invitations', [], false),
        ];
    }
}

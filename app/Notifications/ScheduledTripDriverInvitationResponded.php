<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ScheduledTripDriverInvitation;

class ScheduledTripDriverInvitationResponded extends Notification
{
    use Queueable;

    public $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(ScheduledTripDriverInvitation $invitation)
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
        $trip = $this->invitation->scheduledTrip;
        $tripTitle = $trip && $trip->route 
            ? "Trip #{$trip->id} ({$trip->route->originCity?->name_en} -> {$trip->route->destinationCity?->name_en})"
            : "Scheduled Trip #{$this->invitation->scheduled_trip_id}";

        $isAccepted = $this->invitation->status === 'accepted';
        $titleEn = $isAccepted ? 'Scheduled Trip Invitation Accepted' : 'Scheduled Trip Invitation Declined';

        return [
            'invitation_id'     => $this->invitation->id,
            'scheduled_trip_id' => $this->invitation->scheduled_trip_id,
            'trip_title'        => $tripTitle,
            'driver_id'         => $this->invitation->driver_id,
            'driver_name'       => $driverName,
            'driver_phone'      => $driver->phone ?? '',
            'status'            => $this->invitation->status, // 'accepted' or 'rejected'
            'rejection_reason'  => $this->invitation->rejection_reason,
            'offered_price'     => $this->invitation->offered_price,
            'title_en'          => $titleEn,
            'title_ar'          => $titleEn,
            'message'           => $isAccepted 
                                  ? "Driver {$driverName} accepted invitation for {$tripTitle}."
                                  : "Driver {$driverName} declined invitation for {$tripTitle}.",
            'target_url'        => route('all.scheduled.trip.invitations', [], false),
        ];
    }
}


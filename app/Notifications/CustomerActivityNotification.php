<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerActivityNotification extends Notification
{
    use Queueable;

    public $payload;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'        => $this->payload['title'] ?? 'Notification',
            'body'         => $this->payload['body'] ?? '',
            'type'         => $this->payload['type'] ?? 'system',
            'reference_id' => $this->payload['reference_id'] ?? null,
            'action_route' => $this->payload['action_route'] ?? null,
            'metadata'     => $this->payload['metadata'] ?? null,
        ];
    }
}

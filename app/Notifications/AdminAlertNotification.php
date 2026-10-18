<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminAlertNotification extends Notification
{
    use Queueable;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'             => $this->payload['type'] ?? 'system',
            'title'            => $this->payload['title'] ?? 'Notification',
            'title_en'         => $this->payload['title_en'] ?? ($this->payload['title'] ?? 'Notification'),
            'title_ar'         => $this->payload['title_ar'] ?? ($this->payload['title_en'] ?? ($this->payload['title'] ?? 'Notification')),
            'message'          => $this->payload['message'] ?? ($this->payload['body'] ?? ''),
            'target_url'       => $this->payload['target_url'] ?? null,
            'icon'             => $this->payload['icon'] ?? 'bx-bell',
            'icon_bg'          => $this->payload['icon_bg'] ?? 'rgba(2, 132, 199, 0.15)',
            'icon_color'       => $this->payload['icon_color'] ?? '#0284C7',
            'status'           => $this->payload['status'] ?? 'info',
            'reference_id'     => $this->payload['reference_id'] ?? null,
            'metadata'         => $this->payload['metadata'] ?? [],
        ];
    }
}

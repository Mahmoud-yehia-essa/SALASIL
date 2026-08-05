<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $shipmentId;
    public $status;
    public $locationDescription;
    public $lat;
    public $lng;
    public $timestamp;

    public function __construct(
        int $shipmentId,
        string $status,
        ?string $locationDescription = null,
        ?float $lat = null,
        ?float $lng = null
    ) {
        $this->shipmentId          = $shipmentId;
        $this->status              = $status;
        $this->locationDescription = $locationDescription;
        $this->lat                 = $lat;
        $this->lng                 = $lng;
        $this->timestamp           = now()->format('Y-m-d H:i:s');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('shipment-tracking.' . $this->shipmentId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'shipment.status.updated';
    }
}

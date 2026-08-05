<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $shipmentId;
    public $latitude;
    public $longitude;
    public $speed;
    public $heading;
    public $isStopPoint;
    public $locationDescription;
    public $status;
    public $timestamp;

    public function __construct(
        int $shipmentId,
        float $latitude,
        float $longitude,
        ?float $speed = 0,
        ?int $heading = 0,
        bool $isStopPoint = false,
        ?string $locationDescription = null,
        ?string $status = null
    ) {
        $this->shipmentId          = $shipmentId;
        $this->latitude            = $latitude;
        $this->longitude           = $longitude;
        $this->speed               = $speed ?? 0;
        $this->heading             = $heading ?? 0;
        $this->isStopPoint         = $isStopPoint;
        $this->locationDescription = $locationDescription;
        $this->status              = $status;
        $this->timestamp           = now()->format('Y-m-d H:i:s');
    }

    /**
     * Broadcast on private channel for this specific shipment
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('shipment-tracking.' . $this->shipmentId),
        ];
    }

    /**
     * Broadcast event alias name for Echo client
     */
    public function broadcastAs(): string
    {
        return 'shipment.location.updated';
    }
}

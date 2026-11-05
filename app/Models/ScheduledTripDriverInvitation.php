<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScheduledTrip;
use App\Models\ScheduledTripOrder;
use App\Models\User;
use App\Models\DriverTruck;
use Illuminate\Support\Str;

class ScheduledTripDriverInvitation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scheduledTripOrder()
    {
        return $this->belongsTo(ScheduledTripOrder::class, 'scheduled_trip_order_id');
    }

    public function scheduledTrip()
    {
        return $this->belongsTo(ScheduledTrip::class, 'scheduled_trip_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function driverTruck()
    {
        return $this->belongsTo(DriverTruck::class, 'driver_truck_id');
    }

    public function getMagicLinkAttribute()
    {
        return url('/trip-invite/' . ($this->token ?? $this->id));
    }

    protected static function boot()
    {
        parent::boot();

        // When creating a new invitation, automatically generate a unique 40-char token
        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(40);
            }
        });
    }
}


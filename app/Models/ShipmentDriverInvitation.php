<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Str; // تأكد من استدعاء هذه الفئة

class ShipmentDriverInvitation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
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
        return url('/invite/' . ($this->token ?? $this->id));
    }

    protected static function boot()
    {
        parent::boot();

        // عند إنشاء دعوة جديدة، قم بتوليد Token عشوائي وفريد
        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(40); // يولد نص عشوائي قوي جداً من 40 حرف
            }
        });
    }

}
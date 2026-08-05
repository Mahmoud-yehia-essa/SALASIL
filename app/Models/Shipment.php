<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TruckType;
use App\Models\ScheduledTrip;
use App\Models\Conversation;

use App\Models\ShipmentTracking;
use App\Models\Country;
use App\Models\City;
use App\Models\ShipmentType;
use App\Models\ShipmentNature;

class Shipment extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'customer_id',
    //     'driver_id',
    //     'truck_type_id',
    //     'scheduled_trip_id',
    //     'pickup_address',
    //     'dropoff_address',
    //     'pickup_lat',
    //     'pickup_lng',
    //     'dropoff_lat',
    //     'dropoff_lng',
    //     'scheduled_date',
    //     'goods_description',
    //     'weight',
    //     'price',
    //     'status',
    //     'payment_status',
    //     'payment_method',
    // ];

    use HasFactory;

   

    protected $guarded = [];

    /**
     * علاقة الشحنة بالعميل
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * علاقة الشحنة بالسائق
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * علاقة الشحنة بنوع الشاحنة
     */
    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type_id');
    }

    public function truckSubType()
    {
        return $this->belongsTo(TruckSubType::class, 'truck_sub_type_id');
    }

    /**
     * علاقة الشحنة بالرحلة المجدولة (إن وجدت)
     */
    public function scheduledTrip()
    {
        return $this->belongsTo(ScheduledTrip::class);
    }

    public function trackings()
    {
        return $this->hasMany(ShipmentTracking::class)->orderBy('id', 'desc');
    }

    public function latestTracking()
    {
        return $this->hasOne(ShipmentTracking::class)->latestOfMany();
    }

    public function trackingLogs()
    {
        return $this->hasMany(ShipmentTrackingLog::class, 'shipment_id')->orderBy('id', 'asc');
    }

    public function latestTrackingLog()
    {
        return $this->hasOne(ShipmentTrackingLog::class, 'shipment_id')->latestOfMany();
    }


    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'shipment_id');
    }

    public function shipmentType()
    {
        return $this->belongsTo(ShipmentType::class, 'shipment_type_id');
    }

    public function shipmentNature()
    {
        return $this->belongsTo(ShipmentNature::class, 'shipment_nature_id');
    }

    public function pickupCountry()
    {
        return $this->belongsTo(Country::class, 'pickup_country_id');
    }

    public function pickupCity()
    {
        return $this->belongsTo(City::class, 'pickup_city_id');
    }

    public function dropoffCountry()
    {
        return $this->belongsTo(Country::class, 'dropoff_country_id');
    }

    public function dropoffCity()
    {
        return $this->belongsTo(City::class, 'dropoff_city_id');
    }
}
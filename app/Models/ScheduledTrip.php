<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Route;
use App\Models\TruckType;
use App\Models\User;
use App\Models\TruckSubType;
use App\Models\ShipmentType;
use App\Models\ShipmentNature;

class ScheduledTrip extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'required_documents' => 'array',
        'customs_clearance'  => 'boolean',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function truckType()
    {
        return $this->belongsTo(TruckType::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * نوع الشحنة الافتراضي للرحلة المجدولة (إن وجد)
     */
    public function shipmentType()
    {
        return $this->belongsTo(ShipmentType::class, 'shipment_type_id');
    }

    /**
     * طبيعة الشحنة الافتراضية للرحلة المجدولة (إن وجد)
     */
    public function shipmentNature()
    {
        return $this->belongsTo(ShipmentNature::class, 'shipment_nature_id');
    }


    /**
     * علاقة الرحلة المجدولة بالنوع الفرعي للشاحنة (إن وجد)
     */
    public function truckSubType()
    {
        return $this->belongsTo(TruckSubType::class, 'truck_sub_type_id');
    }

    /**
     * طلبات الحجز المربوطة بهذه الرحلة المجدولة
     */
    public function orders()
    {
        return $this->hasMany(ScheduledTripOrder::class, 'scheduled_trip_id');
    }
}
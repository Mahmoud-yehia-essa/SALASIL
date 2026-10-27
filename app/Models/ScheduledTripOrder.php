<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScheduledTrip;
use App\Models\User;
use App\Models\TruckType;
use App\Models\TruckSubType;
use App\Models\ShipmentType;
use App\Models\ShipmentNature;
use App\Models\Country;
use App\Models\City;
use App\Models\Invoice;

class ScheduledTripOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'documents'         => 'array',
        'special_equipment' => 'array',
        'customs_clearance' => 'boolean',
    ];

    /**
     * Accessor to ensure documents array contains full file_url
     */
    public function getDocumentsAttribute($value)
    {
        $docs = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($docs)) {
            return [];
        }

        return array_map(function ($doc) {
            if (!is_array($doc)) {
                return $doc;
            }
            $path = $doc['file_path'] ?? '';
            if ($path) {
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $doc['file_url'] = $path;
                } else {
                    $cleanPath = preg_replace('#^public/#', '', ltrim($path, '/'));
                    $doc['file_url'] = asset($cleanPath);
                }
            } else {
                $doc['file_url'] = null;
            }
            return $doc;
        }, $docs);
    }

    /**
     * العلاقة مع الرحلة المجدولة
     */
    public function scheduledTrip()
    {
        return $this->belongsTo(ScheduledTrip::class, 'scheduled_trip_id');
    }

    /**
     * العلاقة مع العميل طالب الحجز
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * العلاقة مع السائق (إن وجد)
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * العلاقة مع نوع الشاحنة
     */
    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type_id');
    }

    /**
     * العلاقة مع النوع الفرعي للشاحنة
     */
    public function truckSubType()
    {
        return $this->belongsTo(TruckSubType::class, 'truck_sub_type_id');
    }

    /**
     * العلاقة مع نوع الشحنة
     */
    public function shipmentType()
    {
        return $this->belongsTo(ShipmentType::class, 'shipment_type_id');
    }

    /**
     * العلاقة مع طبيعة الشحنة
     */
    public function shipmentNature()
    {
        return $this->belongsTo(ShipmentNature::class, 'shipment_nature_id');
    }

    /**
     * دولة موقع التحميل
     */
    public function pickupCountry()
    {
        return $this->belongsTo(Country::class, 'pickup_country_id');
    }

    /**
     * مدينة موقع التحميل
     */
    public function pickupCity()
    {
        return $this->belongsTo(City::class, 'pickup_city_id');
    }

    /**
     * دولة موقع التسليم
     */
    public function dropoffCountry()
    {
        return $this->belongsTo(Country::class, 'dropoff_country_id');
    }

    /**
     * مدينة موقع التسليم
     */
    public function dropoffCity()
    {
        return $this->belongsTo(City::class, 'dropoff_city_id');
    }

    /**
     * الفاتورة المرتبطة بالطلب
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'scheduled_trip_order_id');
    }
}

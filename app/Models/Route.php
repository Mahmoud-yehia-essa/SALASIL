<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScheduledTrip;
use App\Models\Country;
use App\Models\City;




class Route extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * المسار يمكن أن يحتوي على عدة رحلات مجدولة
     */
    public function scheduledTrips()
    {
        return $this->hasMany(ScheduledTrip::class);
    }

    /**
     * علاقات نقطة الانطلاق (Origin)
     */
    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function originCity()
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    /**
     * علاقات نقطة الوصول (Destination)
     */
    public function destinationCountry()
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function destinationCity()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }
}
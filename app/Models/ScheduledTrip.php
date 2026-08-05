<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Route;
use App\Models\TruckType;
use App\Models\User;
use App\Models\TruckSubType;

class ScheduledTrip extends Model
{
    use HasFactory;

    protected $guarded = [];

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
     * علاقة الرحلة المجدولة بالنوع الفرعي للشاحنة (إن وجد)
     */
    public function truckSubType()
    {
        return $this->belongsTo(TruckSubType::class, 'truck_sub_type_id');
    }
}
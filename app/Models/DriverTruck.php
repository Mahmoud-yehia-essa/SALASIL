<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User; // تضمين صريح
use App\Models\TruckType;
use App\Models\TruckSubType;





class DriverTruck extends Model
{
    use HasFactory;

    protected $guarded = []; // جعل جميع الحقول قابلة للإدخال

    // علاقة الشاحنة بالسائق
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // علاقة الشاحنة بالنوع الرئيسي
    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type_id');
    }

    // علاقة الشاحنة بالنوع الفرعي (إن وجد)
    public function truckSubType()
    {
        return $this->belongsTo(TruckSubType::class, 'truck_sub_type_id');
    }

    public function truckBrand()
    {
        return $this->belongsTo(TruckBrand::class, 'truck_brand_id');
    }

    public function truckModel()
    {
        return $this->belongsTo(TruckModel::class, 'truck_model_id');
    }
}
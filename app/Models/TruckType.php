<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DriverProfile; // تضمين صريح للنموذج
use App\Models\TruckSubType;



class TruckType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'max_weight',
        'photo',
        'status',
    ];

    /**
     * نوع الشاحنة يمكن أن ينتمي لعدة سائقين
     */
    public function drivers()
    {
        return $this->hasMany(DriverProfile::class);
    }


    // علاقة النوع الرئيسي بأنواعه الفرعية
    public function subTypes()
    {
        return $this->hasMany(TruckSubType::class, 'truck_type_id');
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TruckType;


class TruckSubType extends Model
{
    use HasFactory;

    protected $guarded = [];

    // علاقة النوع الفرعي بالنوع الرئيسي
    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type_id');
    }
}
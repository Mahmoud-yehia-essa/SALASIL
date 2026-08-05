<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;

class ShipmentTrackingLog extends Model
{
    use HasFactory;

    // إخبار لارافيل بعدم البحث عن حقل updated_at لأنه غير موجود في التصميم المعتمد
    const UPDATED_AT = null;

    protected $fillable = [
        'shipment_id',
        'latitude',
        'longitude',
        'is_stop_point',
        'speed',
        'heading',
        'created_at',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}
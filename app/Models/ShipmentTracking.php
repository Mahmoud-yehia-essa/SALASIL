<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;

class ShipmentTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status',
        'location_description',
        'lat',
        'lng',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
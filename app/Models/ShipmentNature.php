<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;


class ShipmentNature extends Model
{
    use HasFactory;

    protected $guarded = []; 

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'shipment_nature_id');
    }
}
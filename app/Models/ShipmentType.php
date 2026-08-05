<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;


class ShipmentType extends Model
{
    use HasFactory;

    protected $guarded = []; // لجعل الحقول قابلة للإدخال

    /**
     * علاقة نوع الشحنة بالطلبات (يجلب كل الطلبات التابعة لهذا النوع)
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'shipment_type_id');
    }
}
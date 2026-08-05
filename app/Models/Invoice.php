<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;
use App\Models\User;
use App\Models\ScheduledTrip;
use App\Models\Payment;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * علاقة الفاتورة بالرحلة المجدولة (إن وجدت)
     */
    public function scheduledTrip()
    {
        return $this->belongsTo(ScheduledTrip::class, 'scheduled_trip_id');
    }

    /**
     * مدفوعات الفاتورة
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
}
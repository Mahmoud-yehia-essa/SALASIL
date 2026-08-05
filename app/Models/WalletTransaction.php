<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Shipment;
use App\Models\ScheduledTrip;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * علاقة العملية بصاحب المحفظة (المستخدم)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة العملية بالشحنة المرتبطة بها (إن وجدت)
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }


    /**
     * علاقة الحركة المالية بالرحلة المجدولة (إن وجدت)
     */
    public function scheduledTrip()
    {
        return $this->belongsTo(ScheduledTrip::class, 'scheduled_trip_id');
    }
}
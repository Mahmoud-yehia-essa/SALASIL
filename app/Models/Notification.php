<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // بما أننا نستخدم UUID، نقوم بتعطيل الترقيم التلقائي
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * علاقة المودل القابل للإشعار (مستخدم، شحنة، إلخ)[cite: 1]
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProofOfDelivery extends Model
{
    use HasFactory;

    protected $table = 'proof_of_deliveries';

    protected $guarded = [];

    protected $casts = [
        'photos'       => 'array',
        'delivered_at' => 'datetime',
    ];

    protected $appends = [
        'photos_urls',
        'signature_url',
    ];

    public function scheduledTripOrder()
    {
        return $this->belongsTo(ScheduledTripOrder::class, 'scheduled_trip_order_id');
    }

    public function scheduledTrip()
    {
        return $this->belongsTo(ScheduledTrip::class, 'scheduled_trip_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Accessor for full URLs of uploaded photos
     */
    public function getPhotosUrlsAttribute()
    {
        $photos = $this->photos;
        if (!is_array($photos)) {
            return [];
        }

        return array_map(function ($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                return $p;
            }
            $clean = preg_replace('#^public/#', '', ltrim($p, '/'));
            return asset($clean);
        }, array_filter($photos));
    }

    /**
     * Accessor for signature image URL
     */
    public function getSignatureUrlAttribute()
    {
        $sig = $this->signature_image;
        if (!$sig) return null;
        if (str_starts_with($sig, 'http://') || str_starts_with($sig, 'https://')) {
            return $sig;
        }
        $clean = preg_replace('#^public/#', '', ltrim($sig, '/'));
        return asset($clean);
    }
}


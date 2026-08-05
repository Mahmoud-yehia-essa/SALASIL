<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;      // تضمين صريح
use App\Models\TruckType; // تضمين صريح

class DriverProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'truck_type_id',
        'license_number',
        'license_photo',
        'truck_registration_photo',
        'civil_id_photo',
        'wallet_balance',
        'rating',
        'availability_status',
        'verification_status',
        'rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function truckType()
    {
        return $this->belongsTo(TruckType::class);
    }
}
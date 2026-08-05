<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'commercial_register',
        'commercial_register_doc',
        'civil_id',
        'tax_number',
        'representative_name',
        'representative_position',
        'representative_phone',
        'verification_status',
        'rejection_reason',
    ];

    /**
     * علاقة الشركة بالمستخدم (One-to-One)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
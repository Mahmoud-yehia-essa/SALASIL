<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CompanyProfile;
use App\Models\DriverProfile;
use App\Models\WalletTransaction;
use App\Models\ConversationParticipant;
use App\Models\DriverTruck;
use App\Models\Country;
use App\Models\City;
use App\Models\Message;


class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_code',
        'fname',
        'lname',
        'name',
        'email',
        'phone',
        'secondary_phone',
        'password',
        'photo',
        'dateofbirth',
        'locale',
        'country_code',
        'country_id',
        'city_id',
        'address',
        'role',
        'status',
        'provider',
        'firebase_token',
        'token',
    ];

    /**
     * Auto-generate unique client code on creation.
     */
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->client_code)) {
                $user->client_code = static::generateClientCode($user->role);
            }
        });
    }

    /**
     * Generate a unique client code based on user role.
     * Roles: driver -> DR-XXXXX, company_customer -> CO-XXXXX, individual_customer -> CU-XXXXX, admin -> ADM-XXXXX
     */
    public static function generateClientCode(?string $role = 'individual_customer'): string
    {
        $prefixMap = [
            'driver'              => 'DR',
            'company_customer'    => 'CO',
            'individual_customer' => 'CU',
            'admin'               => 'ADM',
        ];

        $prefix = $prefixMap[$role ?? ''] ?? 'CU';

        do {
            $number = rand(10000, 99999);
            $code = $prefix . '-' . $number;
        } while (static::where('client_code', $code)->exists());

        return $code;
    }

    /**
     * Get full name from fname and lname.
     */
    public function getNameAttribute(): string
    {
        return trim(($this->fname ?? '') . ' ' . ($this->lname ?? ''));
    }

    /**
     * Set fname and lname from full name string.
     */
    public function setNameAttribute($value): void
    {
        $parts = explode(' ', trim((string) $value), 2);
        $this->attributes['fname'] = $parts[0] ?? '';
        $this->attributes['lname'] = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token',
        'firebase_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'dateofbirth' => 'date',
        ];
    }


    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    
    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }


    /**
     * علاقة المستخدم بالعمليات المالية الخاصة بمحفظته
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }


    public function conversationParticipations()
    {
        return $this->hasMany(ConversationParticipant::class, 'user_id');
    }



    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }


    public function trucks()
    {
        return $this->hasMany(DriverTruck::class, 'driver_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
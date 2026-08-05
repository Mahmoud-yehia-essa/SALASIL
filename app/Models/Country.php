<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class Country extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * علاقة الدولة بالمدن التابعة لها
     */
    public function cities()
    {
        return $this->hasMany(City::class, 'country_id');
    }
}
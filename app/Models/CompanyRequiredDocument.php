<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRequiredDocument extends Model
{
    use HasFactory;

    protected $table = 'company_required_documents';

    protected $fillable = [
        'user_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'user_id'     => 'integer',
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userDocuments()
    {
        return $this->hasMany(UserCompanyDocument::class, 'required_document_id');
    }
}

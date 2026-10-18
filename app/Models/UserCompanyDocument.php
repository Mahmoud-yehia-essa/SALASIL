<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCompanyDocument extends Model
{
    use HasFactory;

    protected $table = 'user_company_documents';

    protected $fillable = [
        'user_id',
        'required_document_id',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status',
        'rejection_reason',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        if (empty($this->file_path)) {
            return null;
        }
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return url($this->file_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requiredDocument()
    {
        return $this->belongsTo(CompanyRequiredDocument::class, 'required_document_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingScreen extends Model
{
    use HasFactory;

    protected $table = "onboarding_screens";

    protected $fillable = [
        "title_en",
        "title_ar",
        "description_en",
        "description_ar",
        "image",
        "sort_order",
        "status",
        "is_active",
    ];

    protected $casts = [
        "sort_order" => "integer",
        "is_active" => "boolean",
    ];

    protected $appends = [
        "image_url",
    ];

    /**
     * Get the full URL to the onboarding image.
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            if (file_exists(public_path("upload/onboarding_images/" . $this->image))) {
                return asset("upload/onboarding_images/" . $this->image);
            }
        }
        return asset("upload/no_image.jpg");
    }

    /**
     * Scope a query to only include active onboarding screens.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where("status", "active")
              ->orWhere("is_active", true);
        });
    }

    /**
     * Scope a query to sort screens by designated order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy("sort_order", "asc")->orderBy("id", "asc");
    }
}

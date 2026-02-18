<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'description',
        'description_ar',
        'description_en',
        'location',
        'location_ar',
        'location_en',
        'cover_path',
        'is_featured_home',
    ];

    protected $casts = [
        'is_featured_home' => 'boolean',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }
}

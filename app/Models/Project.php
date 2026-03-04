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
        'price',
        'status',
        'valuation',
        'yield',
        'property_type',
        'year_built',
        'area_sqft',
        'features',
    ];

    protected $casts = [
        'is_featured_home' => 'boolean',
        'price' => 'integer',
        'valuation' => 'integer',
        'yield' => 'float',
        'property_type' => 'array',
        'year_built' => 'integer',
        'area_sqft' => 'integer',
        'features' => 'array',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }
}

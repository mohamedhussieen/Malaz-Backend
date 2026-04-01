<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'owner_id',
        'owner_name',
        'owner_name_ar',
        'owner_name_en',
        'owner_title',
        'owner_title_ar',
        'owner_title_en',
        'owner_avatar_url',
        'is_featured_home',
        'project_hero_section',
        'price',
        'status',
        'valuation',
        'yield',
        'property_type',
        'year_built',
        'area_sqft',
        'min_investment',
        'down_payment_percentage',
        'years_of_installment',
        'target_fund',
        'funded_amount',
        'cap_rate',
        'cash_on_cash',
        'irr',
        'features',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'is_featured_home' => 'boolean',
        'project_hero_section' => 'boolean',
        'price' => 'integer',
        'valuation' => 'integer',
        'yield' => 'float',
        'property_type' => 'array',
        'year_built' => 'integer',
        'area_sqft' => 'integer',
        'min_investment' => 'integer',
        'down_payment_percentage' => 'float',
        'years_of_installment' => 'integer',
        'target_fund' => 'integer',
        'funded_amount' => 'integer',
        'cap_rate' => 'float',
        'cash_on_cash' => 'float',
        'irr' => 'float',
        'features' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }
}

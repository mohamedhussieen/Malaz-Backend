<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeContent extends Model
{
    protected $fillable = [
        'headline_text',
        'headline_text_ar',
        'headline_text_en',
        'body_text',
        'body_text_ar',
        'body_text_en',
        'youtube_url',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(HomeImage::class)->orderBy('sort_order');
    }
}

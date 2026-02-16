<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'title_ar',
        'title_en',
        'excerpt',
        'excerpt_ar',
        'excerpt_en',
        'content',
        'content_ar',
        'content_en',
        'slug',
        'cover_path',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function paragraphs(): HasMany
    {
        return $this->hasMany(BlogParagraph::class)->orderBy('sort_order')->orderBy('id');
    }
}

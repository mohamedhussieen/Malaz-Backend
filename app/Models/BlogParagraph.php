<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogParagraph extends Model
{
    protected $fillable = [
        'blog_id',
        'header',
        'header_ar',
        'header_en',
        'content',
        'content_ar',
        'content_en',
        'sort_order',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }
}

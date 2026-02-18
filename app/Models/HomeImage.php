<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeImage extends Model
{
    protected $fillable = [
        'home_content_id',
        'name',
        'path',
        'sort_order',
    ];

    public function homeContent(): BelongsTo
    {
        return $this->belongsTo(HomeContent::class);
    }
}

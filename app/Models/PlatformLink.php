<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformLink extends Model
{
    protected $fillable = [
        'key',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'title',
        'title_ar',
        'title_en',
        'bio',
        'bio_ar',
        'bio_en',
        'avatar_path',
    ];
}

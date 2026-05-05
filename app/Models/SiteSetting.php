<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_typing',
        'about_description',
        'about_badges',
        'cv_link',
        'hero_image',
        'about_image',
        'site_logo',
    ];

    protected $casts = [
        'hero_typing' => 'array',
        'about_badges' => 'array',
    ];
}

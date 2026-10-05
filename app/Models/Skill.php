<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'icon_url', 'level', 'sort_order'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($skill) {
            if (empty($skill->sort_order)) {
                $skill->sort_order = static::max('sort_order') + 1;
            }
            
            if (empty($skill->icon_url)) {
                $name = strtolower($skill->name);
                $name = str_replace(' ', '', $name); 
                $skill->icon_url = "https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/{$name}/{$name}-original.svg";
            }
        });
    }
}

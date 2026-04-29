<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class project extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image',
        'url_link',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

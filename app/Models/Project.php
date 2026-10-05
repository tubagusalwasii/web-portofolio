<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image',
        'url_link',
        'tech_stack',
    ];

    /**
     * Tech stack: store as JSON array of strings (e.g. ["Laravel","Kotlin"])
     * Getter: always returns array of strings.
     */
    public function getTechStackAttribute($value): array
    {
        if (!$value) return [];
        if (is_array($value)) return $value;
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    public function setTechStackAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['tech_stack'] = json_encode(array_values(array_filter(array_map('trim', $value))));
        } elseif (is_string($value) && $value !== '') {
            $this->attributes['tech_stack'] = $value;
        } else {
            $this->attributes['tech_stack'] = null;
        }
    }

    /**
     * Getter: Filament Repeater expects array of ['path' => '...'].
     * Also used by $casts-less approach so the model always gives arrays.
     * Raw string  → [['path' => 'file.png']]
     * JSON array  → [['path' => 'a.png'], ['path' => 'b.png']]
     * null / ''   → []
     */
    public function getImageAttribute($value): array
    {
        if (!$value) return [];

        if (is_array($value)) {
            return array_values(array_map(fn ($p) => is_array($p) ? $p : ['path' => $p], $value));
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Already a JSON array of strings → wrap each in ['path' => ...]
            return array_values(array_map(fn ($p) => is_array($p) ? $p : ['path' => $p], $decoded));
        }

        // Plain string (old format)
        return [['path' => $value]];
    }

    /**
     * Setter: Filament Repeater saves array of ['path' => '...']
     * or a flat array of strings. Serialize to JSON flat array.
     */
    public function setImageAttribute($value): void
    {
        if (is_array($value)) {
            // Extract paths and remove nulls
            $paths = array_values(array_filter(array_map(
                fn ($item) => is_array($item) ? ($item['path'] ?? null) : $item,
                $value
            )));
            $this->attributes['image'] = json_encode($paths);
        } elseif (is_string($value)) {
            $this->attributes['image'] = $value;
        } else {
            $this->attributes['image'] = null;
        }
    }

    /**
     * Blade & Table helper: returns a flat array of path strings.
     */
    public function getImagesAttribute(): array
    {
        $raw = $this->image;
        if (is_array($raw)) {
            $paths = [];
            foreach ($raw as $item) {
                if (is_array($item) && isset($item['path'])) {
                    $paths[] = $item['path'];
                } elseif (is_string($item)) {
                    $paths[] = $item;
                }
            }
            return array_values(array_filter($paths));
        }
        if (is_string($raw) && $raw !== '') {
            return [$raw];
        }
        return [];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

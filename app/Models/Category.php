<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
    ];

    /**
     * Get all classifications for this category.
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(VerseClassification::class);
    }

    /**
     * Get stats for this category.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(VerseStat::class);
    }

    /**
     * Get verses through classifications.
     */
    public function verses(): HasManyThrough
    {
        return $this->hasManyThrough(Verse::class, VerseClassification::class, 'category_id', 'id', 'id', 'verse_id');
    }
}

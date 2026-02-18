<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Get all user-verse-category entries for this category (new structure).
     */
    public function userVerseCategories(): HasMany
    {
        return $this->hasMany(UserVerseCategory::class);
    }

    /**
     * Get bible verses classified under this category (new structure).
     */
    public function bibleVerses(): BelongsToMany
    {
        return $this->belongsToMany(BibleVerse::class, 'user_verse_categories', 'category_id', 'bible_verse_id')
            ->withPivot(['user_id', 'device_id'])
            ->withTimestamps();
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

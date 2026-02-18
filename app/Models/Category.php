<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            ->withPivot(['user_id'])
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use App\Models\UserVerseCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BibleVerse extends Model
{
    use HasFactory;

    protected $table = 'bible_verses';

    protected $fillable = [
        'reference',
        'text',
        'version',
    ];

    /**
     * Get all classification pivot entries for this verse.
     */
    public function userCategories(): HasMany
    {
        return $this->hasMany(UserVerseCategory::class, 'bible_verse_id');
    }

    /**
     * Get categories assigned to this verse through the pivot.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_verse_categories', 'bible_verse_id', 'category_id')
            ->withPivot(['user_id', 'device_id'])
            ->withTimestamps();
    }

    /**
     * Get users who classified this verse.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_verse_categories', 'bible_verse_id', 'user_id')
            ->withPivot(['category_id', 'device_id'])
            ->withTimestamps();
    }

    /**
     * Find or create a BibleVerse by reference and version.
     */
    public static function findOrCreateByReference(string $reference, string $version, string $text): self
    {
        return static::firstOrCreate(
            ['reference' => $reference, 'version' => $version],
            ['text' => $text]
        );
    }
}

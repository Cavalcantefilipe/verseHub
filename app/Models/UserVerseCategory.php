<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerseCategory extends Model
{
    use HasFactory;

    protected $table = 'user_verse_categories';

    protected $fillable = [
        'user_id',
        'device_id',
        'bible_verse_id',
        'category_id',
    ];

    /**
     * Get the user who made this classification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bible verse being classified.
     */
    public function bibleVerse(): BelongsTo
    {
        return $this->belongsTo(BibleVerse::class, 'bible_verse_id');
    }

    /**
     * Get the category for this classification.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

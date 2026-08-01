<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    protected $fillable = [
        'bible_verse_id', 'classifiers_count', 'classifications_count',
        'likes_count', 'comments_count', 'status', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return ['last_activity_at' => 'datetime'];
    }

    public function bibleVerse(): BelongsTo
    {
        return $this->belongsTo(BibleVerse::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class);
    }
}

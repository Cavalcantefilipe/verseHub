<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Verse extends Model
{
    use HasFactory;

    protected $fillable = [
        'book',
        'chapter',
        'verse_start',
        'verse_end',
        'version_id',
        'external_id',
    ];

    protected $casts = [
        'chapter' => 'integer',
        'verse_start' => 'integer',
        'verse_end' => 'integer',
    ];

    /**
     * Get the Bible version that owns this verse.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(BibleVersion::class, 'version_id');
    }

    /**
     * Get all classifications for this verse.
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(VerseClassification::class);
    }

    /**
     * Get stats for this verse.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(VerseStat::class);
    }

    /**
     * Get categories through classifications.
     */
    public function categories(): HasManyThrough
    {
        return $this->hasManyThrough(Category::class, VerseClassification::class, 'verse_id', 'id', 'id', 'category_id');
    }

    /**
     * Get verse reference as a string.
     */
    public function getReference(): string
    {
        $reference = "{$this->book} {$this->chapter}:{$this->verse_start}";

        if ($this->verse_end && $this->verse_end !== $this->verse_start) {
            $reference .= "-{$this->verse_end}";
        }

        return $reference;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerseClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verse_id',
        'category_id',
        'confidence_level',
    ];

    protected $casts = [
        'confidence_level' => 'integer',
    ];

    /**
     * Get the user that made this classification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verse being classified.
     */
    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }

    /**
     * Get the category for this classification.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

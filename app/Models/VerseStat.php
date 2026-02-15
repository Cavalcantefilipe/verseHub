<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerseStat extends Model
{
    use HasFactory;

    public $timestamps = false; // Only has updated_at

    protected $fillable = [
        'verse_id',
        'category_id',
        'votes',
    ];

    protected $casts = [
        'votes' => 'integer',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the verse for this stat.
     */
    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }

    /**
     * Get the category for this stat.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

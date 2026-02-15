<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'user_id',
        'reference',
        'text',
        'version',
        'category_ids',
    ];

    protected $casts = [
        'category_ids' => 'array',
    ];

    /**
     * Get the user who made this classification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get categories for this classification.
     */
    public function getCategories()
    {
        return Category::whereIn('id', $this->category_ids)->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'language',
    ];

    /**
     * Get the verses for this Bible version.
     */
    public function verses(): HasMany
    {
        return $this->hasMany(Verse::class, 'version_id');
    }
}

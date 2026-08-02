<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyVerse extends Model
{
    protected $fillable = [
        'publish_date', 'position', 'reference', 'version', 'text',
        'book_abbrev', 'book_name', 'chapter', 'verse_number',
        'is_active', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return ['publish_date' => 'date', 'is_active' => 'boolean'];
    }
}

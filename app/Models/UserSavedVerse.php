<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSavedVerse extends Model
{
    protected $fillable = [
        'user_id', 'reference', 'version', 'text', 'is_favorite', 'highlight_color', 'note',
    ];

    protected function casts(): array
    {
        return ['is_favorite' => 'boolean'];
    }
}

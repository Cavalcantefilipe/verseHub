<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushDevice extends Model
{
    protected $fillable = [
        'user_id', 'token_hash', 'token', 'platform', 'device_name',
        'app_version', 'enabled', 'last_seen_at',
    ];

    protected function casts(): array
    {
        return ['token' => 'encrypted', 'enabled' => 'boolean', 'last_seen_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

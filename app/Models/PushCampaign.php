<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushCampaign extends Model
{
    protected $fillable = [
        'title', 'body', 'audience', 'audience_data', 'data', 'status',
        'scheduled_at', 'sent_at', 'target_count', 'sent_count',
        'failed_count', 'last_error', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'audience_data' => 'array', 'data' => 'array',
            'scheduled_at' => 'datetime', 'sent_at' => 'datetime',
        ];
    }
}

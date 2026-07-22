<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryAuditLog extends Model
{
    use HasFactory;

    protected $table = 'category_audit_log';

    protected $fillable = [
        'target_type',
        'target_id',
        'admin_user_id',
        'action',
        'notes',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}

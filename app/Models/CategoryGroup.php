<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryGroup extends Model
{
    use HasFactory;

    protected $table = 'category_groups';

    protected $fillable = [
        'name',
        'slug',
        'classification_kind',
        'selection_prompt',
        'selection_limit',
        'icon',
        'color',
        'display_order',
        'created_by_user_id',
        'status',
        'approved_by_user_id',
        'approved_at',
        'rejected_reason',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'display_order' => 'integer',
            'selection_limit' => 'integer',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function isOfficial(): bool
    {
        return is_null($this->created_by_user_id);
    }
}

<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;

class AdminAuditService
{
    public function log(User $admin, string $action, string $targetType, ?int $targetId, array $context = []): void
    {
        AdminAuditLog::create([
            'admin_user_id' => $admin->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'context' => $context === [] ? null : $context,
            'created_at' => now(),
        ]);
    }
}

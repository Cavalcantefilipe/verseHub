<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityEvent;

/**
 * Ponto único que escreve em user_activity_events. Mantém o registro
 * desacoplado das regras de gamificação (que ainda não existem) — quando
 * regras forem definidas, a conta de pontos pode ser feita a partir desse
 * histórico.
 */
class ActivityEventService
{
    public const VERSE_CLASSIFIED = 'verse_classified';

    public const CATEGORY_CREATED = 'category_created';

    public const CATEGORY_GROUP_CREATED = 'category_group_created';

    public const CATEGORY_APPROVED = 'category_approved';

    public const CATEGORY_REJECTED = 'category_rejected';

    public const DAILY_LOGIN = 'daily_login';

    public function track(User $user, string $eventType, ?array $data = null): UserActivityEvent
    {
        return UserActivityEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'event_data' => $data,
            'created_at' => now(),
        ]);
    }
}

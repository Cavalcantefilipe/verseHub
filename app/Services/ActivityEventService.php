<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ActivityEventService
{
    public const VERSE_CLASSIFIED = 'verse_classified';

    public const CATEGORY_CREATED = 'category_created';

    public const CATEGORY_GROUP_CREATED = 'category_group_created';

    public const CATEGORY_APPROVED = 'category_approved';

    public const CATEGORY_REJECTED = 'category_rejected';

    public const DAILY_LOGIN = 'daily_login';

    public const COMMENT_CREATED = 'comment_created';

    private const POINTS = [
        self::VERSE_CLASSIFIED => 10,
        self::CATEGORY_CREATED => 2,
        self::CATEGORY_GROUP_CREATED => 2,
        self::CATEGORY_APPROVED => 15,
        self::CATEGORY_REJECTED => 0,
        self::DAILY_LOGIN => 2,
        self::COMMENT_CREATED => 3,
    ];

    public function track(User $user, string $eventType, ?array $data = null): UserActivityEvent
    {
        return DB::transaction(function () use ($user, $eventType, $data) {
            $event = UserActivityEvent::create([
                'user_id' => $user->id,
                'event_type' => $eventType,
                'event_data' => $data,
                'created_at' => now(),
            ]);

            $isNewClassification = $eventType !== self::VERSE_CLASSIFIED
                || (bool) ($data['is_new'] ?? true);
            $points = $isNewClassification ? (self::POINTS[$eventType] ?? 0) : 0;

            DB::table('user_stats')->insertOrIgnore([
                'user_id' => $user->id,
                'total_points' => 0,
                'current_level' => 1,
                'current_streak_days' => 0,
                'longest_streak_days' => 0,
                'classifications_count' => 0,
                'approved_categories_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $stats = DB::table('user_stats')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $today = CarbonImmutable::today();
            $lastActivity = $stats?->last_activity_at
                ? CarbonImmutable::parse($stats->last_activity_at)->startOfDay()
                : null;
            $currentStreak = (int) ($stats?->current_streak_days ?? 0);

            if (! $lastActivity || $lastActivity->lt($today->subDay())) {
                $currentStreak = 1;
            } elseif ($lastActivity->equalTo($today->subDay())) {
                $currentStreak++;
            }

            $totalPoints = (int) ($stats?->total_points ?? 0) + $points;
            $classificationIncrement = $eventType === self::VERSE_CLASSIFIED && $isNewClassification ? 1 : 0;
            $approvedIncrement = $eventType === self::CATEGORY_APPROVED ? 1 : 0;

            DB::table('user_stats')->where('user_id', $user->id)->update([
                'total_points' => $totalPoints,
                'current_level' => $this->levelForPoints($totalPoints),
                'current_streak_days' => $currentStreak,
                'longest_streak_days' => max((int) ($stats?->longest_streak_days ?? 0), $currentStreak),
                'classifications_count' => (int) ($stats?->classifications_count ?? 0) + $classificationIncrement,
                'approved_categories_count' => (int) ($stats?->approved_categories_count ?? 0) + $approvedIncrement,
                'last_activity_at' => now(),
                'updated_at' => now(),
            ]);

            return $event;
        });
    }

    private function levelForPoints(int $points): int
    {
        return max(1, (int) floor(sqrt($points / 25)) + 1);
    }
}

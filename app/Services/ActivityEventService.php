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
            $stats = $this->lockedStats($user);

            $event = UserActivityEvent::create([
                'user_id' => $user->id,
                'event_type' => $eventType,
                'event_data' => $data,
                'created_at' => now(),
            ]);

            $isNewClassification = $eventType !== self::VERSE_CLASSIFIED
                || (bool) ($data['is_new'] ?? true);
            $points = $isNewClassification ? (self::POINTS[$eventType] ?? 0) : 0;

            $today = $eventType === self::DAILY_LOGIN && ! empty($data['activity_date'])
                ? CarbonImmutable::parse($data['activity_date'])->startOfDay()
                : CarbonImmutable::today();
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

    /**
     * Registra no máximo uma abertura por usuário/dia. A chave única da tabela
     * mantém a operação idempotente mesmo com vários aparelhos ou requisições
     * concorrentes.
     */
    public function trackDailyLogin(User $user, string $timezone = 'UTC'): bool
    {
        return DB::transaction(function () use ($user, $timezone) {
            $now = now();
            $date = CarbonImmutable::now($timezone)->toDateString();
            $created = DB::table('user_activity_days')->insertOrIgnore([
                'user_id' => $user->id,
                'activity_date' => $date,
                'first_activity_at' => $now,
                'last_activity_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]) === 1;

            if (! $created) {
                DB::table('user_activity_days')
                    ->where('user_id', $user->id)
                    ->where('activity_date', $date)
                    ->update(['last_activity_at' => $now, 'updated_at' => $now]);

                return false;
            }

            $this->track($user, self::DAILY_LOGIN, ['activity_date' => $date]);

            return true;
        });
    }

    /**
     * Cria o contador materializado apenas uma vez. Usuários anteriores à
     * gamificação são reconstruídos pelo histórico em uma única leitura,
     * evitando zerar progresso já existente.
     */
    private function lockedStats(User $user): object
    {
        $stats = DB::table('user_stats')
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if ($stats) {
            return $stats;
        }

        $baseline = $this->historicalBaseline($user);
        DB::table('user_stats')->insertOrIgnore([
            'user_id' => $user->id,
            ...$baseline,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('user_stats')
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();
    }

    private function historicalBaseline(User $user): array
    {
        $points = 0;
        $classifications = 0;
        $approvedCategories = 0;
        $activityDays = [];

        foreach (DB::table('user_activity_events')->where('user_id', $user->id)->orderBy('id')->cursor() as $event) {
            $data = is_string($event->event_data) ? json_decode($event->event_data, true) : (array) $event->event_data;
            $isRewarded = $event->event_type !== self::VERSE_CLASSIFIED || (bool) ($data['is_new'] ?? true);

            if ($isRewarded) {
                $points += self::POINTS[$event->event_type] ?? 0;
            }
            if ($event->event_type === self::VERSE_CLASSIFIED && $isRewarded) {
                $classifications++;
            }
            if ($event->event_type === self::CATEGORY_APPROVED) {
                $approvedCategories++;
            }

            $day = CarbonImmutable::parse($event->created_at)->toDateString();
            $activityDays[$day] = true;
        }

        $orderedDays = array_keys($activityDays);
        sort($orderedDays);
        $currentStreak = $this->currentStreak($orderedDays);

        return [
            'total_points' => $points,
            'current_level' => $this->levelForPoints($points),
            'current_streak_days' => $currentStreak,
            'longest_streak_days' => $this->longestStreak($orderedDays),
            'classifications_count' => $classifications,
            'approved_categories_count' => $approvedCategories,
            'last_activity_at' => $orderedDays === [] ? null : end($orderedDays).' 00:00:00',
        ];
    }

    private function currentStreak(array $orderedDays): int
    {
        if ($orderedDays === []) {
            return 0;
        }

        $last = CarbonImmutable::parse(end($orderedDays));
        if ($last->lt(CarbonImmutable::today()->subDay())) {
            return 0;
        }

        $streak = 1;
        for ($index = count($orderedDays) - 2; $index >= 0; $index--) {
            $previous = CarbonImmutable::parse($orderedDays[$index]);
            if (! $previous->equalTo($last->subDay())) {
                break;
            }
            $streak++;
            $last = $previous;
        }

        return $streak;
    }

    private function longestStreak(array $orderedDays): int
    {
        $longest = 0;
        $current = 0;
        $previous = null;
        foreach ($orderedDays as $day) {
            $date = CarbonImmutable::parse($day);
            $current = $previous && $date->equalTo($previous->addDay()) ? $current + 1 : 1;
            $longest = max($longest, $current);
            $previous = $date;
        }

        return $longest;
    }

    private function levelForPoints(int $points): int
    {
        return max(1, (int) floor(sqrt($points / 25)) + 1);
    }
}

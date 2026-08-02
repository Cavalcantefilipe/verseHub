<?php

namespace App\Services;

use App\Models\PushCampaign;
use App\Models\PushDevice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

class ExpoPushService
{
    public function sendCampaign(PushCampaign $campaign): array
    {
        $query = PushDevice::query()->where('enabled', true)->orderBy('id');
        $this->applyAudience($query, $campaign);
        $targetCount = (clone $query)->count();
        $sent = 0;
        $failed = 0;

        $query->chunkById(100, function ($devices) use ($campaign, &$sent, &$failed) {
            $messages = $devices->map(fn (PushDevice $device) => [
                'to' => $device->token,
                'title' => $campaign->title,
                'body' => $campaign->body,
                'sound' => 'default',
                'channelId' => 'versehub',
                'data' => $campaign->data ?? [],
            ])->values()->all();

            try {
                $response = Http::acceptJson()
                    ->timeout(15)
                    ->retry(2, 350)
                    ->post('https://exp.host/--/api/v2/push/send', $messages);
                $response->throw();
                $tickets = collect($response->json('data', []));
                $sent += $tickets->where('status', 'ok')->count();
                $failed += max(0, count($messages) - $tickets->where('status', 'ok')->count());
            } catch (\Throwable) {
                $failed += count($messages);
            }
        });

        return compact('targetCount', 'sent', 'failed');
    }

    private function applyAudience(Builder $query, PushCampaign $campaign): void
    {
        if ($campaign->audience === 'public_profiles') {
            $query->whereExists(fn ($subquery) => $subquery
                ->selectRaw('1')
                ->from('user_public_profiles')
                ->whereColumn('user_public_profiles.user_id', 'push_devices.user_id')
                ->where('user_public_profiles.is_public', true));
        }

        if ($campaign->audience === 'users') {
            $ids = collect($campaign->audience_data['user_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique();
            $query->whereIn('user_id', $ids);
        }
    }
}

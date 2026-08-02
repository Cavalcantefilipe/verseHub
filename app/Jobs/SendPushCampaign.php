<?php

namespace App\Jobs;

use App\Models\PushCampaign;
use App\Services\ExpoPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushCampaign implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public function __construct(public int $campaignId) {}

    public function handle(ExpoPushService $push): void
    {
        $campaign = PushCampaign::find($this->campaignId);
        if (! $campaign || in_array($campaign->status, ['sending', 'sent'], true)) return;

        $campaign->update(['status' => 'sending', 'last_error' => null]);
        try {
            $result = $push->sendCampaign($campaign);
            $campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
                'target_count' => $result['targetCount'],
                'sent_count' => $result['sent'],
                'failed_count' => $result['failed'],
            ]);
        } catch (\Throwable $exception) {
            $campaign->update(['status' => 'failed', 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}

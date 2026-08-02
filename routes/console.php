<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendPushCampaign;
use App\Models\PushCampaign;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cachear todos os capítulos da Bíblia no 1º dia de cada mês às 3h
Schedule::command('bible:warm-cache')->monthlyOn(1, '03:00');
Schedule::command('bible:index-search')->monthlyOn(1, '04:00')->withoutOverlapping();

Schedule::call(function () {
    PushCampaign::query()
        ->where('status', 'scheduled')
        ->where('scheduled_at', '<=', now())
        ->orderBy('id')
        ->limit(100)
        ->get(['id'])
        ->each(function (PushCampaign $campaign) {
            if (PushCampaign::whereKey($campaign->id)->where('status', 'scheduled')->update(['status' => 'queued']) === 1) {
                SendPushCampaign::dispatch($campaign->id);
            }
        });
})->everyMinute()->name('dispatch-scheduled-push')->withoutOverlapping();

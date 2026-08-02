<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushCampaign;
use App\Models\PushCampaign;
use App\Models\PushDevice;
use App\Services\AdminAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminPushController extends Controller
{
    public function __construct(private AdminAuditService $audit) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => [
            'devices' => [
                'total' => PushDevice::where('enabled', true)->count(),
                'ios' => PushDevice::where('enabled', true)->where('platform', 'ios')->count(),
                'android' => PushDevice::where('enabled', true)->where('platform', 'android')->count(),
            ],
            'campaigns' => PushCampaign::query()->latest()->paginate(30),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:240'],
            'audience' => ['required', Rule::in(['all', 'public_profiles', 'users'])],
            'audience_data' => ['nullable', 'array'],
            'audience_data.user_ids' => ['nullable', 'array', 'max:500'],
            'audience_data.user_ids.*' => ['integer', 'exists:users,id'],
            'data' => ['nullable', 'array'],
            'data.path' => ['nullable', 'string', 'max:300'],
            'data.reference' => ['nullable', 'string', 'max:120'],
            'data.version' => ['nullable', 'string', 'max:10'],
            'scheduled_at' => ['nullable', 'date'],
            'send_now' => ['nullable', 'boolean'],
        ]);

        $sendNow = (bool) ($validated['send_now'] ?? false);
        unset($validated['send_now']);
        $validated['status'] = $sendNow ? 'queued' : ($validated['scheduled_at'] ?? null ? 'scheduled' : 'draft');
        $validated['created_by_user_id'] = Auth::id();
        $campaign = PushCampaign::create($validated);
        $this->audit->log(Auth::user(), 'push_campaign.created', 'push_campaign', $campaign->id, [
            'audience' => $campaign->audience, 'scheduled_at' => $campaign->scheduled_at,
        ]);

        if ($sendNow) SendPushCampaign::dispatch($campaign->id)->afterCommit();

        return response()->json(['data' => $campaign], 201);
    }

    public function send(PushCampaign $campaign): JsonResponse
    {
        abort_if(in_array($campaign->status, ['queued', 'sending', 'sent'], true), 422, 'Esta campanha já foi enviada ou está na fila.');
        $campaign->update(['status' => 'queued', 'scheduled_at' => null]);
        SendPushCampaign::dispatch($campaign->id)->afterCommit();
        $this->audit->log(Auth::user(), 'push_campaign.queued', 'push_campaign', $campaign->id);

        return response()->json(['data' => $campaign->fresh()]);
    }
}

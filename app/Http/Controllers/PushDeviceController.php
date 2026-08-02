<?php

namespace App\Http\Controllers;

use App\Models\PushDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushDeviceController extends Controller
{
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['required', 'in:ios,android'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:30'],
        ]);

        $device = PushDevice::updateOrCreate(
            ['token_hash' => hash('sha256', $validated['token'])],
            [
                'user_id' => Auth::id(),
                'token' => $validated['token'],
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'enabled' => true,
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['data' => ['id' => $device->id, 'enabled' => true]]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate(['token' => ['required', 'string', 'max:500']]);
        PushDevice::query()
            ->where('user_id', Auth::id())
            ->where('token_hash', hash('sha256', $validated['token']))
            ->update(['enabled' => false, 'last_seen_at' => now()]);

        return response()->json(['message' => 'Notificações desativadas neste aparelho.']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyVerse;
use App\Services\AdminAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminDailyVerseController extends Controller
{
    public function __construct(private AdminAuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $from = $request->date('from')?->toDateString() ?? now()->subDays(7)->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->addDays(45)->toDateString();

        return response()->json(['data' => DailyVerse::query()
            ->whereBetween('publish_date', [$from, $to])
            ->orderBy('publish_date')->orderBy('position')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['created_by_user_id'] = Auth::id();
        $verse = DailyVerse::create($data);
        $this->flushDate($verse->publish_date->toDateString());
        $this->audit->log(Auth::user(), 'daily_verse.created', 'daily_verse', $verse->id, $data);

        return response()->json(['data' => $verse], 201);
    }

    public function update(Request $request, DailyVerse $dailyVerse): JsonResponse
    {
        $oldDate = $dailyVerse->publish_date->toDateString();
        $data = $this->validated($request, $dailyVerse->id);
        $dailyVerse->update($data);
        $this->flushDate($oldDate);
        $this->flushDate($dailyVerse->publish_date->toDateString());
        $this->audit->log(Auth::user(), 'daily_verse.updated', 'daily_verse', $dailyVerse->id, $data);

        return response()->json(['data' => $dailyVerse->fresh()]);
    }

    public function destroy(DailyVerse $dailyVerse): JsonResponse
    {
        $date = $dailyVerse->publish_date->toDateString();
        $id = $dailyVerse->id;
        $dailyVerse->delete();
        $this->flushDate($date);
        $this->audit->log(Auth::user(), 'daily_verse.deleted', 'daily_verse', $id);

        return response()->json([], 204);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'publish_date' => ['required', 'date'],
            'position' => ['required', 'integer', 'min:1', 'max:10', 'unique:daily_verses,position,'.$ignoreId.',id,publish_date,'.$request->input('publish_date')],
            'reference' => ['required', 'string', 'max:120'],
            'version' => ['required', 'string', 'max:10'],
            'text' => ['required', 'string', 'max:5000'],
            'book_abbrev' => ['nullable', 'string', 'max:10'],
            'book_name' => ['nullable', 'string', 'max:100'],
            'chapter' => ['nullable', 'integer', 'min:1', 'max:200'],
            'verse_number' => ['nullable', 'integer', 'min:1', 'max:200'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function flushDate(string $date): void
    {
        Cache::forget("home:v4:{$date}");
        Cache::forget("home:v3:{$date}");
    }
}

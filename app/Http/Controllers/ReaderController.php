<?php

namespace App\Http\Controllers;

use App\Models\UserSavedVerse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReaderController extends Controller
{
    public function readingState(): JsonResponse
    {
        return response()->json(['data' => DB::table('user_reading_states')->where('user_id', Auth::id())->first()]);
    }

    public function updateReadingState(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'version' => 'required|string|max:10',
            'book_abbrev' => 'required|string|max:10',
            'book_name' => 'required|string|max:100',
            'chapter' => 'required|integer|min:1|max:150',
            'verse_number' => 'nullable|integer|min:1|max:200',
        ]);
        DB::table('user_reading_states')->updateOrInsert(
            ['user_id' => Auth::id()],
            [...$validated, 'created_at' => now(), 'updated_at' => now()]
        );

        return $this->readingState();
    }

    public function saved(Request $request): JsonResponse
    {
        $perPage = min(50, max(1, (int) $request->get('per_page', 20)));
        $page = UserSavedVerse::where('user_id', Auth::id())
            ->orderByDesc('updated_at')->orderByDesc('id')->cursorPaginate($perPage);

        return response()->json(['data' => $page->items(), 'meta' => [
            'next_cursor' => $page->nextCursor()?->encode(),
            'has_more' => $page->hasMorePages(),
        ]]);
    }

    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:255',
            'version' => 'required|string|max:10',
            'text' => 'required|string',
            'is_favorite' => 'sometimes|boolean',
            'highlight_color' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:5000',
        ]);
        $saved = UserSavedVerse::updateOrCreate(
            ['user_id' => Auth::id(), 'reference' => $validated['reference'], 'version' => $validated['version']],
            $validated
        );

        return response()->json(['data' => $saved], $saved->wasRecentlyCreated ? 201 : 200);
    }

    public function remove(UserSavedVerse $savedVerse): JsonResponse
    {
        abort_unless($savedVerse->user_id === Auth::id(), 404);
        $savedVerse->delete();

        return response()->json(['message' => 'Versículo removido da biblioteca.']);
    }

    public function profile(Request $request): JsonResponse
    {
        if ($request->isMethod('get')) {
            return response()->json(['data' => DB::table('user_public_profiles')->where('user_id', Auth::id())->first()]);
        }

        $validated = $request->validate([
            'display_name' => 'nullable|string|max:80',
            'bio' => 'nullable|string|max:240',
            'is_public' => 'required|boolean',
            'show_ranking' => 'required|boolean',
        ]);
        DB::table('user_public_profiles')->updateOrInsert(
            ['user_id' => Auth::id()],
            [...$validated, 'avatar_url' => Auth::user()->avatar, 'created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['data' => DB::table('user_public_profiles')->where('user_id', Auth::id())->first()]);
    }

    public function ranking(Request $request): JsonResponse
    {
        $limit = min(50, max(5, (int) $request->get('limit', 20)));
        $rows = DB::table('user_public_profiles as profile')
            ->join('users', 'users.id', '=', 'profile.user_id')
            ->leftJoin('user_stats as stats', 'stats.user_id', '=', 'profile.user_id')
            ->where('profile.is_public', true)->where('profile.show_ranking', true)
            ->orderByDesc('stats.total_points')->orderBy('profile.user_id')
            ->limit($limit)
            ->get(['profile.user_id', 'profile.display_name', 'profile.avatar_url', 'stats.total_points', 'stats.current_level']);

        return response()->json(['data' => $rows->map(fn ($row, $index) => [
            'position' => $index + 1,
            'user_id' => $row->user_id,
            'name' => $row->display_name ?: 'Leitor da comunidade',
            'avatar' => $row->avatar_url,
            'points' => (int) ($row->total_points ?? 0),
            'level' => (int) ($row->current_level ?? 1),
        ])]);
    }
}

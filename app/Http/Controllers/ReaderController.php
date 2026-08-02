<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSavedVerse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $user = Auth::user();
        $profile = DB::table('user_public_profiles')->where('user_id', $user->id)->first();

        if ($request->isMethod('get')) {
            $stats = DB::table('user_stats')->where('user_id', $user->id)->first();

            return response()->json(['data' => [
                'display_name' => $profile?->display_name ?: $user->name,
                'avatar_url' => $profile?->avatar_url ?: $user->avatar,
                'bio' => $profile?->bio,
                'is_public' => (bool) ($profile?->is_public ?? false),
                'show_ranking' => (bool) ($profile?->show_ranking ?? false),
                'stats' => [
                    'points' => (int) ($stats?->total_points ?? 0),
                    'level' => (int) ($stats?->current_level ?? 1),
                    'streak_days' => (int) ($stats?->current_streak_days ?? 0),
                    'classifications' => (int) ($stats?->classifications_count ?? 0),
                ],
            ]]);
        }

        $validated = $request->validate([
            'display_name' => 'nullable|string|max:80',
            'bio' => 'nullable|string|max:240',
            'is_public' => 'required|boolean',
            'show_ranking' => 'required|boolean',
        ]);

        DB::table('user_public_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                ...$validated,
                'avatar_url' => $profile?->avatar_url ?: $user->avatar,
                'created_at' => $profile?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'data' => DB::table('user_public_profiles')->where('user_id', $user->id)->first(),
        ]);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=160,min_height=160,max_width=4096,max_height=4096',
        ]);

        $user = Auth::user();
        $profile = DB::table('user_public_profiles')->where('user_id', $user->id)->first();
        $previous = $profile?->avatar_url;
        $path = $validated['avatar']->storePublicly('avatars/'.$user->id, 'public');
        $avatarUrl = url(Storage::disk('public')->url($path));

        DB::table('user_public_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'display_name' => $profile?->display_name ?: $user->name,
                'avatar_url' => $avatarUrl,
                'bio' => $profile?->bio,
                'is_public' => (bool) ($profile?->is_public ?? false),
                'show_ranking' => (bool) ($profile?->show_ranking ?? false),
                'created_at' => $profile?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );

        if ($previous && str_contains($previous, '/storage/avatars/')) {
            $oldPath = ltrim(str_replace('/storage/', '', (string) parse_url($previous, PHP_URL_PATH)), '/');
            if ($oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return response()->json(['data' => ['avatar_url' => $avatarUrl]]);
    }

    public function publicProfile(User $user): JsonResponse
    {
        $profile = DB::table('user_public_profiles')->where('user_id', $user->id)->first();
        abort_unless($profile?->is_public, 404);

        $stats = DB::table('user_stats')->where('user_id', $user->id)->first();
        $impact = DB::table('community_posts as post')
            ->where('post.status', 'published')
            ->whereExists(function ($query) use ($user) {
                $query->selectRaw('1')
                    ->from('user_verse_categories as uvc')
                    ->whereColumn('uvc.bible_verse_id', 'post.bible_verse_id')
                    ->where('uvc.user_id', $user->id);
            })
            ->selectRaw('COALESCE(SUM(post.likes_count), 0) as likes_received')
            ->selectRaw('COALESCE(SUM(post.comments_count), 0) as comments_received')
            ->first();

        $recent = DB::table('user_verse_categories as uvc')
            ->join('bible_verses as verse', 'verse.id', '=', 'uvc.bible_verse_id')
            ->leftJoin('community_posts as post', 'post.bible_verse_id', '=', 'verse.id')
            ->where('uvc.user_id', $user->id)
            ->groupBy('verse.id', 'verse.reference', 'verse.text', 'verse.version')
            ->orderByRaw('MAX(uvc.created_at) DESC')
            ->limit(10)
            ->get([
                'verse.id',
                'verse.reference',
                'verse.text',
                'verse.version',
                DB::raw('MAX(uvc.created_at) as shared_at'),
                DB::raw('COALESCE(MAX(post.likes_count), 0) as likes_count'),
                DB::raw('COALESCE(MAX(post.comments_count), 0) as comments_count'),
            ]);

        return response()->json(['data' => [
            'user_id' => $user->id,
            'name' => $profile->display_name ?: $user->name,
            'avatar' => $profile->avatar_url ?: $user->avatar,
            'bio' => $profile->bio,
            'stats' => [
                'points' => (int) ($stats?->total_points ?? 0),
                'level' => (int) ($stats?->current_level ?? 1),
                'streak_days' => (int) ($stats?->current_streak_days ?? 0),
                'classifications' => (int) ($stats?->classifications_count ?? 0),
                'likes_received' => (int) ($impact?->likes_received ?? 0),
                'comments_received' => (int) ($impact?->comments_received ?? 0),
            ],
            'recent_contributions' => $recent,
        ]]);
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

<?php

namespace App\Http\Controllers;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Services\ActivityEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommunityController extends Controller
{
    public function __construct(
        protected ActivityEventService $activityEventService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'category_ids' => ['nullable', 'string', 'regex:/^\d+(,\d+){0,9}$/'],
            'sort' => ['nullable', Rule::in(['popular', 'recent', 'oldest', 'liked', 'commented'])],
            'per_page' => 'nullable|integer|min:1|max:30',
            'cursor' => 'nullable|string',
        ]);

        $sort = $validated['sort'] ?? 'popular';
        $perPage = (int) ($validated['per_page'] ?? 15);
        $query = CommunityPost::query()
            ->join('bible_verses as bv', 'bv.id', '=', 'community_posts.bible_verse_id')
            ->where('community_posts.status', 'published')
            ->select([
                'community_posts.*', 'bv.reference', 'bv.text', 'bv.version',
            ]);

        $categoryIds = ! empty($validated['category_ids'])
            ? collect(explode(',', $validated['category_ids']))->map(fn ($id) => (int) $id)->unique()->values()->all()
            : (! empty($validated['category_id']) ? [(int) $validated['category_id']] : []);
        if ($categoryIds !== []) {
            $query->whereExists(function ($subquery) use ($categoryIds) {
                $subquery->selectRaw('1')
                    ->from('user_verse_categories as filtered_uvc')
                    ->join('categories as filtered_category', 'filtered_category.id', '=', 'filtered_uvc.category_id')
                    ->whereColumn('filtered_uvc.bible_verse_id', 'community_posts.bible_verse_id')
                    ->whereIn('filtered_uvc.category_id', $categoryIds)
                    ->where('filtered_category.status', 'approved');
            });
        }

        $page = match ($sort) {
            'recent' => $query
                ->orderByDesc('community_posts.last_activity_at')
                ->orderByDesc('community_posts.id')
                ->cursorPaginate($perPage),
            'oldest' => $query
                ->orderBy('community_posts.created_at')
                ->orderBy('community_posts.id')
                ->cursorPaginate($perPage),
            'liked' => $query
                ->orderByDesc('community_posts.likes_count')
                ->orderByDesc('community_posts.id')
                ->cursorPaginate($perPage),
            'commented' => $query
                ->orderByDesc('community_posts.comments_count')
                ->orderByDesc('community_posts.id')
                ->cursorPaginate($perPage),
            default => $query
                ->orderByDesc('community_posts.classifiers_count')
                ->orderByDesc('community_posts.id')
                ->cursorPaginate($perPage),
        };
        $postIds = collect($page->items())->pluck('id');
        $verseIds = collect($page->items())->pluck('bible_verse_id');

        $categoryRows = DB::table('user_verse_categories as uvc')
            ->join('categories as c', function ($join) {
                $join->on('c.id', '=', 'uvc.category_id')->where('c.status', 'approved');
            })
            ->whereIn('uvc.bible_verse_id', $verseIds)
            ->groupBy('uvc.bible_verse_id', 'c.id', 'c.name', 'c.icon', 'c.color')
            ->selectRaw('uvc.bible_verse_id, c.id, c.name, c.icon, c.color, COUNT(*) as aggregate_count')
            ->orderByDesc('aggregate_count')
            ->get()
            ->groupBy('bible_verse_id');

        $userId = Auth::guard('api')->user()?->id;
        $likedPostIds = $userId
            ? DB::table('community_likes')->where('user_id', $userId)->whereIn('community_post_id', $postIds)->pluck('community_post_id')->flip()
            : collect();

        $items = collect($page->items())->map(function ($post) use ($categoryRows, $likedPostIds) {
            $people = max(1, (int) $post->classifiers_count);
            $categories = $categoryRows->get($post->bible_verse_id, collect())->take(3)->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color,
                'count' => (int) $category->aggregate_count,
                'percentage' => round(((int) $category->aggregate_count / $people) * 100, 1),
            ])->values();

            return [
                'id' => $post->id,
                'reference' => $post->reference,
                'text' => $post->text,
                'version' => $post->version,
                'total_people' => (int) $post->classifiers_count,
                'likes_count' => (int) $post->likes_count,
                'comments_count' => (int) $post->comments_count,
                'is_liked' => $likedPostIds->has($post->id),
                'top_categories' => $categories,
                'last_classified_at' => $post->last_activity_at,
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'next_cursor' => $page->nextCursor()?->encode(),
                'has_more' => $page->hasMorePages(),
                'per_page' => $perPage,
                'sort' => $sort,
            ],
        ]);
    }

    public function like(CommunityPost $post): JsonResponse
    {
        $userId = Auth::id();
        $created = false;
        DB::transaction(function () use ($post, $userId, &$created) {
            $created = DB::table('community_likes')->insertOrIgnore([
                'community_post_id' => $post->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]) === 1;
            if ($created) {
                CommunityPost::whereKey($post->id)->increment('likes_count');
            }
        });

        return response()->json(['data' => [
            'liked' => true,
            'likes_count' => $post->fresh()->likes_count,
        ]], $created ? 201 : 200);
    }

    public function unlike(CommunityPost $post): JsonResponse
    {
        DB::transaction(function () use ($post) {
            $deleted = DB::table('community_likes')
                ->where('community_post_id', $post->id)
                ->where('user_id', Auth::id())
                ->delete();
            if ($deleted) {
                $locked = CommunityPost::whereKey($post->id)->lockForUpdate()->firstOrFail();
                $locked->update(['likes_count' => max(0, $locked->likes_count - 1)]);
            }
        });

        return response()->json(['data' => [
            'liked' => false,
            'likes_count' => $post->fresh()->likes_count,
        ]]);
    }

    public function comments(Request $request, CommunityPost $post): JsonResponse
    {
        $perPage = min(30, max(1, (int) $request->get('per_page', 20)));
        $page = CommunityComment::query()
            ->leftJoin('user_public_profiles as profile', function ($join) {
                $join->on('profile.user_id', '=', 'community_comments.user_id')->where('profile.is_public', true);
            })
            ->join('users', 'users.id', '=', 'community_comments.user_id')
            ->where('community_comments.community_post_id', $post->id)
            ->where('community_comments.status', 'published')
            ->select([
                'community_comments.id', 'community_comments.body', 'community_comments.created_at',
                'community_comments.user_id', 'profile.display_name', 'profile.avatar_url', 'profile.is_public', 'users.name',
            ])
            ->orderByDesc('community_comments.id')
            ->cursorPaginate($perPage);

        $items = collect($page->items())->map(fn ($comment) => [
            'id' => $comment->id,
            'body' => $comment->body,
            'created_at' => $comment->created_at,
            'author' => [
                'id' => $comment->is_public ? $comment->user_id : null,
                'name' => $comment->is_public ? ($comment->display_name ?: $comment->name) : 'Leitor da comunidade',
                'avatar' => $comment->is_public ? $comment->avatar_url : null,
                'is_public' => (bool) $comment->is_public,
            ],
        ]);

        return response()->json(['data' => $items, 'meta' => [
            'next_cursor' => $page->nextCursor()?->encode(),
            'has_more' => $page->hasMorePages(),
        ]]);
    }

    public function storeComment(Request $request, CommunityPost $post): JsonResponse
    {
        $validated = $request->validate(['body' => 'required|string|min:2|max:1000']);
        $comment = DB::transaction(function () use ($post, $validated) {
            $comment = CommunityComment::create([
                'community_post_id' => $post->id,
                'user_id' => Auth::id(),
                'body' => trim($validated['body']),
                'status' => 'published',
            ]);
            CommunityPost::whereKey($post->id)->update([
                'comments_count' => DB::raw('comments_count + 1'),
                'last_activity_at' => now(),
            ]);

            return $comment;
        });

        $this->activityEventService->track(
            Auth::user(),
            ActivityEventService::COMMENT_CREATED,
            ['community_post_id' => $post->id, 'comment_id' => $comment->id],
        );

        return response()->json(['data' => $comment], 201);
    }

    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['post', 'comment'])],
            'id' => 'required|integer|min:1',
            'reason' => ['required', Rule::in(['spam', 'abuse', 'hate', 'misinformation', 'other'])],
            'details' => 'nullable|string|max:500',
        ]);
        $table = $validated['type'] === 'post' ? 'community_posts' : 'community_comments';
        abort_unless(DB::table($table)->where('id', $validated['id'])->exists(), 404);

        DB::table('community_reports')->updateOrInsert([
            'reporter_id' => Auth::id(),
            'reportable_type' => $validated['type'],
            'reportable_id' => $validated['id'],
        ], [
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Denúncia recebida. Obrigado por ajudar a comunidade.'], 201);
    }
}

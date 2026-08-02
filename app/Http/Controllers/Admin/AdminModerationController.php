<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Services\AdminAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminModerationController extends Controller
{
    public function __construct(private AdminAuditService $audit) {}

    public function reports(Request $request): JsonResponse
    {
        $status = $request->query('status', 'pending');
        $query = DB::table('community_reports as reports')
            ->join('users as reporter', 'reporter.id', '=', 'reports.reporter_id')
            ->select('reports.*', 'reporter.name as reporter_name', 'reporter.email as reporter_email')
            ->orderByDesc('reports.created_at');
        if ($status !== 'all') $query->where('reports.status', $status);

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function resolveReport(Request $request, int $report): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(['reviewed', 'dismissed', 'actioned'])]]);
        $updated = DB::table('community_reports')->where('id', $report)->update([
            'status' => $validated['status'], 'updated_at' => now(),
        ]);
        abort_unless($updated, 404);
        $this->audit->log(Auth::user(), 'report.'.$validated['status'], 'community_report', $report);

        return response()->json(['message' => 'Denúncia atualizada.']);
    }

    public function posts(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $query = CommunityPost::query()
            ->join('bible_verses as verse', 'verse.id', '=', 'community_posts.bible_verse_id')
            ->select('community_posts.*', 'verse.reference', 'verse.text', 'verse.version')
            ->orderByDesc('community_posts.last_activity_at');
        if ($search !== '') $query->where(fn ($q) => $q->where('verse.reference', 'like', "%{$search}%")->orWhere('verse.text', 'like', "%{$search}%"));

        return response()->json(['data' => $query->paginate(40)]);
    }

    public function updatePost(Request $request, CommunityPost $post): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(['published', 'hidden', 'removed'])]]);
        $post->update($validated);
        $this->audit->log(Auth::user(), 'post.'.$validated['status'], 'community_post', $post->id);

        return response()->json(['data' => $post->fresh()]);
    }

    public function comments(Request $request): JsonResponse
    {
        $query = CommunityComment::query()
            ->join('users', 'users.id', '=', 'community_comments.user_id')
            ->join('community_posts', 'community_posts.id', '=', 'community_comments.community_post_id')
            ->join('bible_verses', 'bible_verses.id', '=', 'community_posts.bible_verse_id')
            ->select([
                'community_comments.*', 'users.name as user_name', 'users.email as user_email',
                'bible_verses.reference',
            ])->orderByDesc('community_comments.id');
        if ($request->filled('status')) $query->where('community_comments.status', $request->query('status'));
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(fn ($q) => $q->where('community_comments.body', 'like', "%{$search}%")->orWhere('bible_verses.reference', 'like', "%{$search}%"));
        }

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function updateComment(Request $request, CommunityComment $comment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['published', 'hidden', 'removed'])],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($comment, $validated) {
            $wasPublished = $comment->status === 'published';
            $comment->update($validated);
            $isPublished = $comment->status === 'published';
            if ($wasPublished !== $isPublished) {
                $post = CommunityPost::whereKey($comment->community_post_id)->lockForUpdate()->firstOrFail();
                $post->update(['comments_count' => max(0, $post->comments_count + ($isPublished ? 1 : -1))]);
            }
        });
        $this->audit->log(Auth::user(), 'comment.updated', 'community_comment', $comment->id, $validated);

        return response()->json(['data' => $comment->fresh()]);
    }
}

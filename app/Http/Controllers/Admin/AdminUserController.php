<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminService;
use App\Services\AdminAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminService $adminService,
        protected AdminAuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter');
        $search = $request->query('search');

        $query = User::query()
            ->select(['id', 'name', 'email', 'avatar', 'is_admin', 'is_banned', 'banned_at', 'banned_reason', 'can_create_categories', 'custom_categories_count', 'created_at'])
            ->with('stats:user_id,total_points,current_level,current_streak_days,classifications_count')
            ->orderByDesc('created_at');

        if ($filter === 'admins') {
            $query->where('is_admin', true);
        } elseif ($filter === 'blocked') {
            $query->where('can_create_categories', false);
        } elseif ($filter === 'banned') {
            $query->where('is_banned', true);
        } elseif ($filter === 'top_creators') {
            $query->orderByDesc('custom_categories_count');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['stats', 'pushDevices' => fn ($query) => $query->select('id', 'user_id', 'platform', 'device_name', 'app_version', 'enabled', 'last_seen_at')])
            ->loadCount(['customCategories', 'customCategoryGroups', 'activityEvents']);

        return response()->json(['data' => $user]);
    }

    public function updateIdentity(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'min:2', 'max:80'],
            'avatar' => ['sometimes', 'nullable', 'url', 'max:2048'],
        ]);
        $before = $user->only(['name', 'avatar']);
        $user->update($validated);
        if (array_key_exists('name', $validated) || array_key_exists('avatar', $validated)) {
            \Illuminate\Support\Facades\DB::table('user_public_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'display_name' => $validated['name'] ?? $user->name,
                    'avatar_url' => array_key_exists('avatar', $validated) ? $validated['avatar'] : $user->avatar,
                    'updated_at' => now(), 'created_at' => now(),
                ],
            );
        }
        $this->auditService->log(Auth::user(), 'user.identity_updated', 'user', $user->id, ['before' => $before, 'after' => $validated]);

        return response()->json(['data' => $user->fresh()]);
    }

    public function setBan(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'banned' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        abort_if($user->id === Auth::id() && $validated['banned'], 422, 'Você não pode banir sua própria conta.');
        $user->update([
            'is_banned' => $validated['banned'],
            'banned_at' => $validated['banned'] ? now() : null,
            'banned_reason' => $validated['banned'] ? ($validated['reason'] ?? 'Restrição administrativa') : null,
        ]);
        if ($validated['banned']) {
            $user->pushDevices()->update(['enabled' => false]);
        }
        $this->auditService->log(Auth::user(), $validated['banned'] ? 'user.banned' : 'user.unbanned', 'user', $user->id, ['reason' => $validated['reason'] ?? null]);

        return response()->json(['data' => $user->fresh()]);
    }

    public function adjustPoints(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'points' => ['required', 'integer', 'min:-100000', 'max:100000'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);
        $stats = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated) {
            \Illuminate\Support\Facades\DB::table('user_stats')->insertOrIgnore([
                'user_id' => $user->id, 'total_points' => 0, 'current_level' => 1,
                'current_streak_days' => 0, 'longest_streak_days' => 0,
                'classifications_count' => 0, 'approved_categories_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $stats = \App\Models\UserStats::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $total = max(0, $stats->total_points + $validated['points']);
            $stats->update(['total_points' => $total, 'current_level' => max(1, (int) floor(sqrt($total / 25)) + 1)]);

            return $stats->fresh();
        });
        $this->auditService->log(Auth::user(), 'user.points_adjusted', 'user', $user->id, $validated);

        return response()->json(['data' => $stats]);
    }

    public function blockCategories(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = $this->adminService->blockCreator(Auth::user(), $user, $validated['reason'] ?? null);

        return response()->json([
            'data' => $user,
            'message' => 'Usuário bloqueado de criar categorias.',
        ]);
    }

    public function unblockCategories(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $this->adminService->unblockCreator(Auth::user(), $user, $validated['notes'] ?? null);

        return response()->json([
            'data' => $user,
            'message' => 'Usuário liberado para criar categorias.',
        ]);
    }

    public function promote(User $user): JsonResponse
    {
        $user = $this->adminService->promoteAdmin(Auth::user(), $user);

        return response()->json([
            'data' => $user,
            'message' => 'Usuário promovido a administrador.',
        ]);
    }

    public function demote(User $user): JsonResponse
    {
        $admin = Auth::user();

        if ($admin->id === $user->id) {
            return response()->json([
                'message' => 'Você não pode rebaixar a si mesmo.',
            ], 422);
        }

        $user = $this->adminService->demoteAdmin($admin, $user);

        return response()->json([
            'data' => $user,
            'message' => 'Usuário rebaixado.',
        ]);
    }
}

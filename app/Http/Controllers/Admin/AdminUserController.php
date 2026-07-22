<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminService $adminService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter');
        $search = $request->query('search');

        $query = User::query()
            ->select(['id', 'name', 'email', 'avatar', 'is_admin', 'can_create_categories', 'custom_categories_count', 'created_at'])
            ->orderByDesc('created_at');

        if ($filter === 'admins') {
            $query->where('is_admin', true);
        } elseif ($filter === 'blocked') {
            $query->where('can_create_categories', false);
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
        $user->loadCount(['customCategories', 'customCategoryGroups']);

        return response()->json(['data' => $user]);
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

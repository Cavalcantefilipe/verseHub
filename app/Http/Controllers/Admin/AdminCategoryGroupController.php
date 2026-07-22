<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryGroup;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminCategoryGroupController extends Controller
{
    public function __construct(
        protected AdminService $adminService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $query = CategoryGroup::with('creator:id,name,email')
            ->withCount('categories')
            ->orderBy('display_order');

        if ($status && in_array($status, ['approved', 'pending', 'rejected'], true)) {
            $query->where('status', $status);
        }

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function pending(): JsonResponse
    {
        $items = CategoryGroup::with('creator:id,name,email')
            ->withCount('categories')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json(['data' => $items]);
    }

    public function approve(Request $request, CategoryGroup $categoryGroup): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $group = $this->adminService->approveGroup(
            Auth::user(),
            $categoryGroup,
            $validated['notes'] ?? null
        );

        return response()->json([
            'data' => $group,
            'message' => 'Grupo aprovado.',
        ]);
    }

    public function reject(Request $request, CategoryGroup $categoryGroup): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $group = $this->adminService->rejectGroup(
            Auth::user(),
            $categoryGroup,
            $validated['reason'] ?? null
        );

        return response()->json([
            'data' => $group,
            'message' => 'Grupo rejeitado e categorias filhas pendentes também foram rejeitadas.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer',
        ]);

        $admin = Auth::user();

        $group = CategoryGroup::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? '#6b7280',
            'display_order' => $validated['display_order'] ?? 0,
            'created_by_user_id' => null,
            'status' => 'approved',
            'approved_by_user_id' => $admin->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'data' => $group,
            'message' => 'Grupo oficial criado.',
        ], 201);
    }

    public function update(Request $request, CategoryGroup $categoryGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer',
        ]);

        $categoryGroup->update($validated);

        return response()->json([
            'data' => $categoryGroup->fresh(),
            'message' => 'Grupo atualizado.',
        ]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'grupo';
        $slug = $base;
        $i = 1;
        while (CategoryGroup::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

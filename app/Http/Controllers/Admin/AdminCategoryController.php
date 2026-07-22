<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function __construct(
        protected AdminService $adminService
    ) {}

    /**
     * GET /api/admin/categories
     * Lista categorias com filtros de status, grupo e busca textual.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $groupId = $request->query('category_group_id');
        $search = $request->query('search');

        $query = Category::with(['group', 'creator:id,name,email'])
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['approved', 'pending', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if ($groupId) {
            $query->where('category_group_id', (int) $groupId);
        }

        if ($search) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return response()->json([
            'data' => $query->paginate(50),
        ]);
    }

    public function pending(): JsonResponse
    {
        $items = Category::with(['group', 'creator:id,name,email'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json(['data' => $items]);
    }

    public function approve(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $category = $this->adminService->approveCategory(
            Auth::user(),
            $category,
            $validated['notes'] ?? null
        );

        return response()->json([
            'data' => $category->load('group'),
            'message' => 'Categoria aprovada.',
        ]);
    }

    public function reject(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $category = $this->adminService->rejectCategory(
            Auth::user(),
            $category,
            $validated['reason'] ?? null
        );

        return response()->json([
            'data' => $category->load('group'),
            'message' => 'Categoria rejeitada.',
        ]);
    }

    /**
     * POST /api/admin/categories — admin cria categoria oficial direto.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'category_group_id' => 'required|integer|exists:category_groups,id',
            'display_order' => 'nullable|integer',
        ]);

        $admin = Auth::user();

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? '#6b7280',
            'category_group_id' => $validated['category_group_id'],
            'display_order' => $validated['display_order'] ?? 0,
            'created_by_user_id' => null,
            'status' => 'approved',
            'approved_by_user_id' => $admin->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'data' => $category->load('group'),
            'message' => 'Categoria oficial criada.',
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'category_group_id' => 'nullable|integer|exists:category_groups,id',
            'display_order' => 'nullable|integer',
        ]);

        $category->update($validated);

        return response()->json([
            'data' => $category->fresh()->load('group'),
            'message' => 'Categoria atualizada.',
        ]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'categoria';
        $slug = $base;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

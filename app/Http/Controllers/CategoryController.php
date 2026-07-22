<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * GET /api/categories
     * Retorna categorias agrupadas por grupo (só status=approved).
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->categoryService->getApprovedGrouped(),
            'message' => 'Categories retrieved successfully',
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        abort_unless($category->status === 'approved', 404);

        $category->loadCount(['userVerseCategories as classifications_count']);
        $category->load('group');
        $category->makeHidden([
            'created_by_user_id',
            'approved_by_user_id',
            'rejected_reason',
        ]);
        $category->group?->makeHidden([
            'created_by_user_id',
            'approved_by_user_id',
            'rejected_reason',
        ]);

        return response()->json([
            'data' => $category,
            'message' => 'Category retrieved successfully',
        ]);
    }

    /**
     * GET /api/categories/mine — categorias custom do usuário (auth).
     */
    public function mine(): JsonResponse
    {
        return response()->json([
            'data' => $this->categoryService->getMineForUser(Auth::user()),
            'message' => 'Custom categories retrieved successfully',
        ]);
    }

    /**
     * POST /api/categories/custom — cria custom (auth).
     */
    public function storeCustom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'category_group_id' => [
                'nullable',
                'integer',
                Rule::exists('category_groups', 'id')->where('status', 'approved'),
            ],
            'new_group_name' => 'nullable|string|max:120',
        ]);

        if (empty($validated['category_group_id']) && empty($validated['new_group_name'])) {
            throw ValidationException::withMessages([
                'group' => 'Escolha um grupo existente ou crie um grupo novo.',
            ]);
        }

        if (! empty($validated['category_group_id']) && ! empty($validated['new_group_name'])) {
            throw ValidationException::withMessages([
                'group' => 'Escolha apenas uma opção: grupo existente ou novo.',
            ]);
        }

        $category = $this->categoryService->createCustom(Auth::user(), $validated);
        $category->load('group');

        return response()->json([
            'data' => $category,
            'message' => 'Categoria criada e enviada para aprovação.',
        ], 201);
    }

    /**
     * DELETE /api/categories/custom/{id} — só pendentes do próprio usuário.
     */
    public function destroyCustom(Category $category): JsonResponse
    {
        $this->categoryService->deleteCustom(Auth::user(), $category);

        return response()->json([
            'message' => 'Categoria removida.',
        ]);
    }
}

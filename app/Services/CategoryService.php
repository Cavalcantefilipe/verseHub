<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public const MAX_CUSTOM_PER_USER = 20;

    public function __construct(
        protected ActivityEventService $activityEventService
    ) {}

    /**
     * Aprovadas (oficiais e de comunidade), agrupadas por grupo aprovado.
     * Categorias sem grupo aparecem em "outros".
     */
    public function getApprovedGrouped(): array
    {
        $groups = CategoryGroup::where('status', 'approved')
            ->select([
                'id',
                'name',
                'slug',
                'icon',
                'color',
                'display_order',
                'status',
                'created_at',
                'updated_at',
            ])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $categories = Category::where('status', 'approved')
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'icon',
                'color',
                'category_group_id',
                'status',
                'display_order',
                'created_at',
                'updated_at',
            ])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category_group_id');

        $result = [];
        foreach ($groups as $group) {
            $result[] = [
                'group' => $group,
                'categories' => $categories->get($group->id, collect())->values(),
            ];
        }

        $orphans = $categories->get(null, collect());
        if ($orphans->isNotEmpty()) {
            $result[] = [
                'group' => null,
                'categories' => $orphans->values(),
            ];
        }

        return $result;
    }

    /**
     * Custom do usuário (todos os status menos rejected, que some).
     */
    public function getMineForUser(User $user): Collection
    {
        return Category::where('created_by_user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->with('group')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Cria categoria custom. Aceita escolher grupo existente OU criar grupo novo.
     * Tudo numa transação pra evitar inconsistência (categoria sem grupo).
     */
    public function createCustom(User $user, array $payload): Category
    {
        if (! $user->can_create_categories) {
            throw ValidationException::withMessages([
                'permission' => 'Você está bloqueado de criar novas categorias.',
            ]);
        }

        $current = $user->custom_categories_count;
        if ($current >= self::MAX_CUSTOM_PER_USER) {
            throw ValidationException::withMessages([
                'limit' => 'Você já criou o número máximo de itens ('.self::MAX_CUSTOM_PER_USER.').',
            ]);
        }

        return DB::transaction(function () use ($user, $payload) {
            $groupId = $payload['category_group_id'] ?? null;
            $newGroupName = $payload['new_group_name'] ?? null;

            if ($newGroupName) {
                // grupo custom também conta no limite — checa antes de criar
                $current = $user->custom_categories_count;
                if ($current + 1 >= self::MAX_CUSTOM_PER_USER) {
                    throw ValidationException::withMessages([
                        'limit' => 'Criar um grupo novo conta no limite. Você não tem espaço para criar grupo + categoria.',
                    ]);
                }

                $group = CategoryGroup::create([
                    'name' => $newGroupName,
                    'slug' => $this->uniqueGroupSlug($newGroupName),
                    'created_by_user_id' => $user->id,
                    'status' => 'pending',
                ]);
                $groupId = $group->id;

                $user->increment('custom_categories_count');
                $this->activityEventService->track(
                    $user,
                    ActivityEventService::CATEGORY_GROUP_CREATED,
                    ['category_group_id' => $group->id]
                );
            }

            $category = Category::create([
                'name' => $payload['name'],
                'slug' => $this->uniqueCategorySlug($payload['name']),
                'description' => $payload['description'] ?? null,
                'icon' => $payload['icon'] ?? null,
                'color' => $payload['color'] ?? '#6b7280',
                'category_group_id' => $groupId,
                'created_by_user_id' => $user->id,
                'status' => 'pending',
            ]);

            $user->increment('custom_categories_count');
            $this->activityEventService->track(
                $user,
                ActivityEventService::CATEGORY_CREATED,
                ['category_id' => $category->id]
            );

            return $category;
        });
    }

    /**
     * Apaga categoria custom. Só funciona se ainda for 'pending' e for do próprio usuário.
     * Aprovadas viram da comunidade — não pode mais deletar.
     */
    public function deleteCustom(User $user, Category $category): void
    {
        if ($category->created_by_user_id !== $user->id) {
            throw ValidationException::withMessages([
                'ownership' => 'Você não criou essa categoria.',
            ]);
        }

        if ($category->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Categorias aprovadas não podem ser excluídas.',
            ]);
        }

        DB::transaction(function () use ($user, $category) {
            $group = $category->group;

            $category->delete();
            $user->decrement('custom_categories_count');

            if ($group
                && $group->created_by_user_id === $user->id
                && $group->status === 'pending'
                && ! $group->categories()->exists()) {
                $group->delete();
                $user->decrement('custom_categories_count');
            }
        });
    }

    public function getById(int $id): ?Category
    {
        return Category::where('id', $id)->where('status', 'approved')->first();
    }

    protected function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'categoria';
        $slug = $base;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function uniqueGroupSlug(string $name): string
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

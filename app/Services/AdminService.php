<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryAuditLog;
use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Operações exclusivas de administradores: aprovar/rejeitar categorias e
 * grupos, bloquear/promover usuários. Todas as ações deixam rastro em
 * category_audit_log.
 */
class AdminService
{
    public function __construct(
        protected ActivityEventService $activityEventService
    ) {}

    public function approveCategory(User $admin, Category $category, ?string $notes = null): Category
    {
        return DB::transaction(function () use ($admin, $category, $notes) {
            // Categoria não pode ser aprovada se o grupo dela ainda está pendente.
            if ($category->category_group_id) {
                $group = CategoryGroup::find($category->category_group_id);
                if ($group && $group->status === 'pending') {
                    $this->approveGroup($admin, $group, 'aprovado em cascata pela categoria');
                }
            }

            $category->update([
                'status' => 'approved',
                'approved_by_user_id' => $admin->id,
                'approved_at' => now(),
                'rejected_reason' => null,
            ]);

            $this->log($admin, 'category', $category->id, 'approved', $notes);

            if ($category->created_by_user_id) {
                $creator = User::find($category->created_by_user_id);
                if ($creator) {
                    $this->activityEventService->track(
                        $creator,
                        ActivityEventService::CATEGORY_APPROVED,
                        ['category_id' => $category->id]
                    );
                }
            }

            return $category->fresh();
        });
    }

    public function rejectCategory(User $admin, Category $category, ?string $reason = null): Category
    {
        return DB::transaction(function () use ($admin, $category, $reason) {
            $category->update([
                'status' => 'rejected',
                'approved_by_user_id' => $admin->id,
                'approved_at' => null,
                'rejected_reason' => $reason,
            ]);

            $this->log($admin, 'category', $category->id, 'rejected', $reason);

            if ($category->created_by_user_id) {
                $creator = User::find($category->created_by_user_id);
                if ($creator) {
                    $this->activityEventService->track(
                        $creator,
                        ActivityEventService::CATEGORY_REJECTED,
                        ['category_id' => $category->id, 'reason' => $reason]
                    );
                }
            }

            return $category->fresh();
        });
    }

    public function approveGroup(User $admin, CategoryGroup $group, ?string $notes = null): CategoryGroup
    {
        return DB::transaction(function () use ($admin, $group, $notes) {
            $group->update([
                'status' => 'approved',
                'approved_by_user_id' => $admin->id,
                'approved_at' => now(),
                'rejected_reason' => null,
            ]);

            $this->log($admin, 'category_group', $group->id, 'approved', $notes);

            return $group->fresh();
        });
    }

    /**
     * Rejeição de grupo é em cascata: todas as categorias filhas pendentes
     * também viram rejected (decisão #5 da estrutura).
     */
    public function rejectGroup(User $admin, CategoryGroup $group, ?string $reason = null): CategoryGroup
    {
        return DB::transaction(function () use ($admin, $group, $reason) {
            $group->update([
                'status' => 'rejected',
                'approved_by_user_id' => $admin->id,
                'approved_at' => null,
                'rejected_reason' => $reason,
            ]);

            $this->log($admin, 'category_group', $group->id, 'rejected', $reason);

            // Cascata: rejeita todas as filhas que ainda estavam pending.
            $childIds = Category::where('category_group_id', $group->id)
                ->where('status', 'pending')
                ->pluck('id');

            foreach ($childIds as $childId) {
                $child = Category::find($childId);
                if ($child) {
                    $this->rejectCategory(
                        $admin,
                        $child,
                        $reason ? "Grupo rejeitado: {$reason}" : 'Grupo rejeitado'
                    );
                }
            }

            return $group->fresh();
        });
    }

    public function blockCreator(User $admin, User $target, ?string $reason = null): User
    {
        $target->update(['can_create_categories' => false]);
        $this->log($admin, 'user', $target->id, 'blocked_creator', $reason);

        return $target->fresh();
    }

    public function unblockCreator(User $admin, User $target, ?string $notes = null): User
    {
        $target->update(['can_create_categories' => true]);
        $this->log($admin, 'user', $target->id, 'unblocked_creator', $notes);

        return $target->fresh();
    }

    public function promoteAdmin(User $admin, User $target): User
    {
        $target->update(['is_admin' => true]);
        $this->log($admin, 'user', $target->id, 'promoted_admin', null);

        return $target->fresh();
    }

    public function demoteAdmin(User $admin, User $target): User
    {
        $target->update(['is_admin' => false]);
        $this->log($admin, 'user', $target->id, 'demoted_admin', null);

        return $target->fresh();
    }

    protected function log(User $admin, string $type, int $id, string $action, ?string $notes): void
    {
        CategoryAuditLog::create([
            'target_type' => $type,
            'target_id' => $id,
            'admin_user_id' => $admin->id,
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}

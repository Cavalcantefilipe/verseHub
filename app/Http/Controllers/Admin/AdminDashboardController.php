<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAuditLog;
use App\Models\CategoryGroup;
use App\Models\User;
use App\Models\UserActivityEvent;
use App\Models\UserVerseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard — contadores rápidos pra home do painel.
     */
    public function index(): JsonResponse
    {
        $today = now()->startOfDay();

        return response()->json([
            'data' => [
                'pending_categories' => Category::where('status', 'pending')->count(),
                'pending_groups' => CategoryGroup::where('status', 'pending')->count(),
                'total_approved_categories' => Category::where('status', 'approved')->count(),
                'total_approved_groups' => CategoryGroup::where('status', 'approved')->count(),
                'total_users' => User::count(),
                'total_admins' => User::where('is_admin', true)->count(),
                'blocked_creators' => User::where('can_create_categories', false)->count(),
                'classifications_today' => UserVerseCategory::where('created_at', '>=', $today)->count(),
                'active_users_today' => UserActivityEvent::where('created_at', '>=', $today)
                    ->distinct('user_id')
                    ->count('user_id'),
                'classifications_total' => UserVerseCategory::count(),
            ],
        ]);
    }

    /**
     * GET /api/admin/audit-log
     */
    public function auditLog(Request $request): JsonResponse
    {
        $targetType = $request->query('target_type');
        $action = $request->query('action');

        $query = CategoryAuditLog::with('admin:id,name,email')
            ->orderByDesc('created_at');

        if ($targetType) {
            $query->where('target_type', $targetType);
        }

        if ($action) {
            $query->where('action', $action);
        }

        return response()->json(['data' => $query->paginate(100)]);
    }
}

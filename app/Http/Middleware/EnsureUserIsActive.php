<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = JWTAuth::parseToken()->authenticate();
        $freshUser = $user ? User::query()->find($user->id) : null;
        if ($freshUser?->is_banned) {
            return response()->json([
                'message' => 'Esta conta está temporariamente restrita.',
                'reason' => $freshUser->banned_reason,
            ], 403);
        }

        return $next($request);
    }
}

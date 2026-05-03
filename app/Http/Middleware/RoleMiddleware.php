<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Load role if not already loaded
        $user->loadMissing('role');

        if (! $user->role || ! in_array($user->role->slug, $roles)) {
            return response()->json(['message' => 'Unauthorized. Insufficient permissions.'], 403);
        }

        return $next($request);
    }
}
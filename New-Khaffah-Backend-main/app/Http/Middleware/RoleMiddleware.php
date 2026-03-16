<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Kalau pakai spatie/permission, pakai hasRole()
        if (method_exists($user, 'hasRole')) {
            if (!$user->hasRole($role)) {
                return response()->json(['message' => 'Forbidden: role mismatch'], 403);
            }
        } else {
            // fallback manual
            $roles = $user->roles;

            if ($roles instanceof \Illuminate\Support\Collection) {
                if (!$roles->pluck('name')->contains($role)) {
                    return response()->json(['message' => 'Forbidden: role mismatch'], 403);
                }
            } elseif (is_array($roles)) {
                if (!in_array($role, $roles)) {
                    return response()->json(['message' => 'Forbidden: role mismatch'], 403);
                }
            } else {
                if ($user->role !== $role) {
                    return response()->json(['message' => 'Forbidden: role mismatch'], 403);
                }
            }
        }

        return $next($request);
    }
}

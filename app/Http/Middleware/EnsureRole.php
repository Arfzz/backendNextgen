<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:mentor')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userRole = $user->role instanceof UserRole
            ? $user->role->value
            : $user->role;

        if ($userRole !== $role) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => "Forbidden. This endpoint requires role: {$role}.",
                ], 403);
            }
            abort(403, "Anda tidak memiliki akses halaman ini.");
        }

        return $next($request);
    }
}

<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($user->role !== $role) {
            return response()->json([
                'message' => "Akses ditolak. Hanya {$role} yang diperbolehkan."
            ], 403);
        }

        return $next($request);
    }
}

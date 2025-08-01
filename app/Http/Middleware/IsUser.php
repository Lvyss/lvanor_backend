<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($user->role !== 'user') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya user yang diperbolehkan.'
            ], 403);
        }

        return $next($request);
    }
}

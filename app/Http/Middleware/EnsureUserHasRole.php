<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user || $user->hotel_id === null) {
            return response()->json(['message' => 'Usuário não associado a nenhum hotel.'], 403);
        }

        $allowedRoles = config('hotel.roles.'.$role, []);

        if (!in_array($user->role, $allowedRoles, true)) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        return $next($request);
    }
}

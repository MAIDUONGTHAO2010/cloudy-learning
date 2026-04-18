<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserSiteAccess
{
    /**
     * Only students and instructors may access user-site authenticated routes.
     * Admins are redirected; unauthenticated requests get a 401.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! in_array($user->role, [UserRole::STUDENT, UserRole::INSTRUCTOR], true)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        return $next($request);
    }
}

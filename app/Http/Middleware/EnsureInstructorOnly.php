<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ((int) $user->role !== UserRole::INSTRUCTOR) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReadOnlyMode
{
    /**
     * Routes (URI patterns) that are still allowed even in read-only mode.
     * Supports exact strings or simple prefix matches.
     */
    private const ALLOWED_WRITE_URIS = [
        'auth/login',
        'auth/logout',
        'admin/login',
        'admin/logout',
    ];

    /**
     * Block all write HTTP methods when READ_ONLY_MODE is enabled,
     * except for login / logout endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.read_only_mode')) {
            return $next($request);
        }

        $isSafeMethod = in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);

        if ($isSafeMethod) {
            return $next($request);
        }

        foreach (self::ALLOWED_WRITE_URIS as $uri) {
            if ($request->is($uri)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'The application is currently in read-only mode. Write operations are disabled.',
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}

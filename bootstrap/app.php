<?php

use App\Http\Middleware\Admin\EnsureAdminValid;
use App\Http\Middleware\EnsureInstructorOnly;
use App\Http\Middleware\EnsureUserSiteAccess;
use App\Http\Middleware\ReadOnlyMode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ReadOnlyMode::class);

        $middleware->validateCsrfTokens(except: [
            'auth/*',
            'api/*',
        ]);

        $middleware->alias([
            'admin.valid' => EnsureAdminValid::class,
            'user.site'   => EnsureUserSiteAccess::class,
            'instructor'  => EnsureInstructorOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

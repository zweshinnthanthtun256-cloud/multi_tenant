<?php

use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsureSubscription;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->authenticateSessions();
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'active' => EnsureActiveAccount::class,
            'subscription' => EnsureSubscription::class,
        ]);
    })->withExceptions(function (Exceptions $exceptions): void {})->create();

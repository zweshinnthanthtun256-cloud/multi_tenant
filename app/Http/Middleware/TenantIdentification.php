<?php

namespace App\Http\Middleware;

use App\Support\Workspace;
use Closure;
use Illuminate\Http\Request;

// Shared database: tenant ownership is enforced by Workspace and scoped queries.
class TenantIdentification
{
    public function handle(Request $request, Closure $next)
    {
        Workspace::id();

        return $next($request);
    }
}

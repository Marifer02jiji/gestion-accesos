<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware as SpatieRoleMiddleware;

class VerificarRol
{
    /**
     * Handle an incoming request by delegating to Spatie's RoleMiddleware.
     */
    public function handle(Request $request, Closure $next, $roles = null)
    {
        $middleware = new SpatieRoleMiddleware();
        return $middleware->handle($request, $next, $roles);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActivationCheckMiddleware
{
    public function handle(Request $request, Closure $next, $area = null): mixed
    {
        return $next($request);
    }
}

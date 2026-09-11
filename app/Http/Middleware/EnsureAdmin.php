<?php

namespace App\Http\Middleware;

use App\Http\Resources\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('Forbidden. Admin access required.', 403);
            }

            abort(403, 'Only admin can access this page.');
        }

        return $next($request);
    }
}
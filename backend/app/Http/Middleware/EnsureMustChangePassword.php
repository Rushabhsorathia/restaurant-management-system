<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $allowedRoutes = [
            'api.v1.auth.change-password',
            'api.v1.auth.logout',
        ];

        if ($user && $user->must_change_password && ! $request->routeIs($allowedRoutes)) {
            return response()->json([
                'success' => false,
                'code' => 'password_change_required',
                'message' => 'You must change your password before continuing.',
            ], 403);
        }

        return $next($request);
    }
}

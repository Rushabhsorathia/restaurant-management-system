<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Outlet;
use App\Support\ActiveOutlet;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active outlet from the X-Outlet-Id header (or request input)
 * and validates the authenticated user can access it. Sets the outlet in the
 * ActiveOutlet request-scoped service.
 *
 * This middleware does NOT require an outlet header (endpoints that are not
 * outlet-scoped should still work). Apply it on outlet-scoped route groups.
 */
class TenantScope
{
    public function __construct(protected ActiveOutlet $activeOutlet)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $outletId = $request->header('X-Outlet-Id');

        if (! $user || ! $outletId) {
            return $next($request);
        }

        $outlet = Outlet::query()
            ->where('id', (int) $outletId)
            ->where('restaurant_id', $user->restaurant_id)
            ->first();

        if (! $outlet) {
            return response()->json([
                'success' => false,
                'message' => 'The selected outlet does not belong to your restaurant.',
            ], 403);
        }

        $assigned = $user->outlets()->where('outlets.id', $outlet->id)->exists();
        if (! $assigned && ! $user->hasRole('hq_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this outlet.',
            ], 403);
        }

        $this->activeOutlet->set($outlet);

        return $next($request);
    }
}
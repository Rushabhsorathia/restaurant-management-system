<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Outlet;
use Illuminate\Support\Facades\Context;

/**
 * Request-scoped holder for the currently active outlet.
 *
 * The TenantScope middleware resolves the X-Outlet-Id header and calls
 * ActiveOutlet::set(). Other services can read it via ActiveOutlet::current()
 * to scope queries (menu, tables, orders) to the active outlet.
 */
class ActiveOutlet
{
    public const CONTEXT_KEY = 'active.outlet';

    public function set(Outlet $outlet): void
    {
        Context::addHidden(self::CONTEXT_KEY, $outlet->id);
        app()->instance(self::CONTEXT_KEY, $outlet);
    }

    public function clear(): void
    {
        Context::forget(self::CONTEXT_KEY);
        app()->forgetInstance(self::CONTEXT_KEY);
    }

    public function current(): ?Outlet
    {
        if (app()->bound(self::CONTEXT_KEY)) {
            return app(self::CONTEXT_KEY);
        }

        return null;
    }

    public function currentId(): ?int
    {
        return Context::getHidden(self::CONTEXT_KEY);
    }
}
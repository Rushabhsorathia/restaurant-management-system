<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Roles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $grouped = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $payload = $grouped->map(function ($perms, $module): array {
            return [
                'module' => $module,
                'permissions' => PermissionResource::collection($perms)->resolve(request()),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'modules' => $payload,
        ]);
    }
}

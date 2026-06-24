<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Roles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return RoleResource::collection($roles);
    }

    public function permissions(Request $request, Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'role' => (new RoleResource($role))->resolve($request),
            'permissions' => PermissionResource::collection($role->permissions)->resolve($request),
        ]);
    }

    public function updatePermissions(Request $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($data['permissions']);
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'role' => (new RoleResource($role))->resolve($request),
        ]);
    }
}

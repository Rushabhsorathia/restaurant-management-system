<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Users\StoreUserRequest;
use App\Http\Requests\Api\V1\Users\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $perPage = (int) min((int) $request->input('per_page', 25), 100);

        $query = User::query()
            ->with(['roles', 'outlets'])
            ->orderBy('name');

        // Tenant scoping: non-HQ admins only see users in their restaurant.
        if (! $actor->hasRole('hq_admin') && $actor->restaurant_id) {
            $query->where('restaurant_id', $actor->restaurant_id);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($outletId = $request->input('outlet_id')) {
            $query->whereHas('outlets', fn ($q) => $q->where('outlets.id', (int) $outletId));
        }

        $active = $request->input('is_active');
        if ($active !== null && $active !== '') {
            $query->where('is_active', filter_var($active, FILTER_VALIDATE_BOOLEAN));
        }

        return UserResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $actor = $request->user();
        $data = $request->validated();

        $restaurantId = $data['restaurant_id'] ?? $actor->restaurant_id;

        $user = DB::transaction(function () use ($data, $restaurantId): User {
            $user = User::create([
                'restaurant_id' => $restaurantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
                'must_change_password' => $data['must_change_password'] ?? true,
            ]);

            $user->syncRoles($data['roles']);
            $user->outlets()->sync($data['outlets']);

            return $user;
        });

        $user->load(['roles', 'outlets', 'permissions']);

        return (new UserResource($user))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, User $user): UserResource
    {
        $this->authorize('view', $user);
        $user->load(['roles', 'outlets', 'permissions']);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        DB::transaction(function () use ($user, $data): void {
            $user->fill(collect($data)->only([
                'name', 'email', 'phone', 'avatar_url', 'is_active',
                'must_change_password', 'restaurant_id',
            ])->toArray());

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }
            if (isset($data['outlets'])) {
                $user->outlets()->sync($data['outlets']);
            }
        });

        $user->load(['roles', 'outlets', 'permissions']);

        return (new UserResource($user))->additional(['success' => true]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($request->user()->id === $user->id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }
}

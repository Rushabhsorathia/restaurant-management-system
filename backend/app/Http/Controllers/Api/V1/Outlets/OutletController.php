<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Outlets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Outlets\StoreOutletRequest;
use App\Http\Requests\Api\V1\Outlets\UpdateOutletRequest;
use App\Http\Resources\Api\V1\FullOutletResource;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OutletController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Outlet::class);

        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404);

        $query = $restaurant->outlets();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        $outlets = $query->orderBy('name')->get();

        return FullOutletResource::collection($outlets);
    }

    public function store(StoreOutletRequest $request): JsonResponse
    {
        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404);

        $outlet = $restaurant->outlets()->create($request->validated());

        // Auto-assign the creator to the new outlet so they can access it.
        $request->user()?->outlets()->syncWithoutDetaching([$outlet->id]);

        return (new FullOutletResource($outlet))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorize('view', $outlet);

        return response()->json([
            'success' => true,
            'outlet' => (new FullOutletResource($outlet))->resolve($request),
        ]);
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): JsonResponse
    {
        $this->authorize('update', $outlet);

        $outlet->update($request->validated());

        return response()->json([
            'success' => true,
            'outlet' => (new FullOutletResource($outlet->fresh()))->resolve($request),
        ]);
    }

    public function status(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorize('update', $outlet);

        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $outlet->forceFill(['is_active' => $data['is_active']])->save();

        return response()->json([
            'success' => true,
            'is_active' => (bool) $outlet->is_active,
        ]);
    }
}
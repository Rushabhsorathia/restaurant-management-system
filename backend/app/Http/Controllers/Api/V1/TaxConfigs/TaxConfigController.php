<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TaxConfigs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TaxConfigs\StoreTaxConfigRequest;
use App\Http\Requests\Api\V1\TaxConfigs\UpdateTaxConfigRequest;
use App\Http\Resources\Api\V1\TaxConfigResource;
use App\Models\TaxConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaxConfigController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', TaxConfig::class);

        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404);

        $configs = $restaurant->taxConfigs()->orderBy('name')->get();

        return TaxConfigResource::collection($configs);
    }

    public function store(StoreTaxConfigRequest $request): JsonResponse
    {
        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404);

        $data = $request->validated();
        $data['restaurant_id'] = $restaurant->id;

        $config = TaxConfig::create($data);

        return (new TaxConfigResource($config))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaxConfigRequest $request, TaxConfig $taxConfig): JsonResponse
    {
        $this->authorize('update', $taxConfig);

        $taxConfig->update($request->validated());

        return response()->json([
            'success' => true,
            'tax_config' => (new TaxConfigResource($taxConfig->fresh()))->resolve($request),
        ]);
    }

    public function destroy(Request $request, TaxConfig $taxConfig): JsonResponse
    {
        $this->authorize('delete', $taxConfig);

        $taxConfig->delete();

        return response()->json(['success' => true, 'message' => 'Tax config deleted.']);
    }
}
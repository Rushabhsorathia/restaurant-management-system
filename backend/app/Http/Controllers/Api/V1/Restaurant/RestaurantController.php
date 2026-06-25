<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Restaurant\UpdateRestaurantRequest;
use App\Http\Requests\Api\V1\Restaurant\UploadLogoRequest;
use App\Http\Resources\Api\V1\RestaurantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404, 'No restaurant associated with your account.');

        return response()->json([
            'success' => true,
            'restaurant' => (new RestaurantResource($restaurant))->resolve($request),
        ]);
    }

    public function update(UpdateRestaurantRequest $request): JsonResponse
    {
        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404);

        $restaurant->update($request->validated());

        return response()->json([
            'success' => true,
            'restaurant' => (new RestaurantResource($restaurant->fresh()))->resolve($request),
        ]);
    }

    public function uploadLogo(UploadLogoRequest $request): JsonResponse
    {
        $restaurant = $request->user()?->restaurant;
        abort_unless($restaurant, 404);

        $file = $request->file('logo');
        $path = $file->store('logos', 'public');

        if ($restaurant->logo_url) {
            Storage::disk('public')->delete($restaurant->logo_url);
        }

        $restaurant->forceFill(['logo_url' => $path])->save();

        return response()->json([
            'success' => true,
            'logo_url' => asset('storage/'.$path),
        ]);
    }
}
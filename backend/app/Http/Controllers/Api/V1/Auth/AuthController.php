<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // Do not reveal which field was wrong.
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ])->status(422);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact your administrator.',
            ], 403);
        }

        if (! $user->restaurant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not associated with any restaurant.',
            ], 403);
        }

        $deviceName = $request->input('device_name') ?: ($request->userAgent() ?? 'unknown');
        $token = $user->createToken($deviceName);

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        $user->load(['roles', 'outlets', 'restaurant']);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'must_change_password' => (bool) $user->must_change_password,
            'user' => (new UserResource($user->load('permissions')))->resolve($request),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['roles', 'permissions', 'outlets', 'restaurant']);

        return response()->json([
            'success' => true,
            'must_change_password' => (bool) $user->must_change_password,
            'user' => (new UserResource($user))->resolve($request),
        ]);
    }
}

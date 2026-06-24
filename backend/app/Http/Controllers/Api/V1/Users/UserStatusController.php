<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserStatusController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $this->authorize('toggleStatus', $user);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own active status.',
            ], 422);
        }

        $user->forceFill(['is_active' => $data['is_active']])->save();

        return response()->json([
            'success' => true,
            'is_active' => (bool) $user->is_active,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserResetPasswordController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $this->authorize('resetPassword', $user);

        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => __($status),
        ]);
    }
}

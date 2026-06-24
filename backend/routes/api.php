<?php

declare(strict_types=1);

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::middleware('throttle:5,1')->group(function (): void {
            Route::post('login', [AuthController::class, 'login'])->name('api.v1.auth.login');
            Route::post('forgot-password', [PasswordResetController::class, 'forgot'])->name('api.v1.auth.forgot-password');
        });

        Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('api.v1.auth.reset-password');

        Route::middleware(['auth:sanctum', 'must.change.password'])->group(function (): void {
            Route::get('me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('change-password', ChangePasswordController::class)->name('api.v1.auth.change-password');
        });
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

<?php

declare(strict_types=1);

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Roles\PermissionController;
use App\Http\Controllers\Api\V1\Roles\RoleController;
use App\Http\Controllers\Api\V1\Users\UserController;
use App\Http\Controllers\Api\V1\Users\UserResetPasswordController;
use App\Http\Controllers\Api\V1\Users\UserStatusController;
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

    Route::middleware(['auth:sanctum', 'must.change.password'])->group(function (): void {
        // Users
        Route::get('users', [UserController::class, 'index'])->name('api.v1.users.index');
        Route::post('users', [UserController::class, 'store'])->name('api.v1.users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('api.v1.users.show');
        Route::put('users/{user}', [UserController::class, 'update'])->name('api.v1.users.update');
        Route::patch('users/{user}/status', UserStatusController::class)->name('api.v1.users.status');
        Route::post('users/{user}/reset-password', UserResetPasswordController::class)->name('api.v1.users.reset-password');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('api.v1.users.destroy');

        // Roles & permissions
        Route::get('roles', [RoleController::class, 'index'])->name('api.v1.roles.index');
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('api.v1.roles.permissions');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('api.v1.roles.update-permissions');
        Route::get('permissions', PermissionController::class)->name('api.v1.permissions.index');
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

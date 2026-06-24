<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\ApiResetPasswordNotification;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    protected function seedContext(): array
    {
        $restaurant = Restaurant::factory()->create();
        $outlet = Outlet::factory()->create(['restaurant_id' => $restaurant->id]);
        $user = User::factory()->create([
            'restaurant_id' => $restaurant->id,
            'email' => 'cashier@rms.local',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $user->outlets()->attach($outlet->id);
        $user->assignRole('cashier');

        return compact('restaurant', 'outlet', 'user');
    }

    public function test_login_with_valid_credentials_returns_token_and_user(): void
    {
        ['user' => $user] = $this->seedContext();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'cashier@rms.local',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'token',
            'must_change_password',
            'user' => [
                'id', 'name', 'email', 'phone', 'is_active',
                'roles', 'permissions', 'outlets',
            ],
        ]);
        $this->assertNotEmpty($response->json('token'));
        $this->assertContains('cashier', $response->json('user.roles'));
        $this->assertTrue($user->fresh()->last_login_at !== null);
    }

    public function test_login_with_invalid_credentials_returns_422_without_user_enumeration(): void
    {
        $this->seedContext();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'cashier@rms.local',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertSame(
            'These credentials do not match our records.',
            $response->json('errors.email.0')
        );
    }

    public function test_login_with_unknown_email_returns_same_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@rms.local',
            'password' => 'whatever',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $this->seedContext();
        User::where('email', 'cashier@rms.local')->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'cashier@rms.local',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('inactive', strtolower((string) $response->json('message')));
    }

    public function test_me_returns_authenticated_user_with_roles_and_outlets(): void
    {
        ['user' => $user] = $this->seedContext();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('user.email', $user->email);
        $response->assertJsonPath('user.roles.0', 'cashier');
        $response->assertJsonCount(1, 'user.outlets');
    }

    public function test_logout_invalidates_current_token(): void
    {
        ['user' => $user] = $this->seedContext();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Sanctum caches the authenticated user across requests in tests;
        // forget the guard so the next request re-validates the token hash.
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    public function test_change_password_requires_current_and_clears_must_change_flag(): void
    {
        ['user' => $user] = $this->seedContext();
        $user->forceFill(['must_change_password' => true])->save();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'password123',
                'password' => 'newPass1!',
                'password_confirmation' => 'newPass1!',
            ]);

        $response->assertOk();
        $this->assertFalse((bool) $user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('newPass1!', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        ['user' => $user] = $this->seedContext();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'wrong',
                'password' => 'newPass1!',
                'password_confirmation' => 'newPass1!',
            ])
            ->assertStatus(422);
    }

    public function test_must_change_password_middleware_blocks_protected_routes(): void
    {
        ['user' => $user] = $this->seedContext();
        $user->forceFill(['must_change_password' => true])->save();
        $token = $user->createToken('test')->plainTextToken;

        // change-password should still work
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(403)
            ->assertJsonPath('code', 'password_change_required');
    }

    public function test_forgot_password_dispatches_notification(): void
    {
        Notification::fake();
        ['user' => $user] = $this->seedContext();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'If the email exists, a reset link has been sent.');

        Notification::assertSentTo($user, ApiResetPasswordNotification::class);
    }

    public function test_forgot_password_succeeds_silently_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nobody@rms.local',
        ]);

        $response->assertOk();
        Notification::assertNothingSent();
    }

    public function test_reset_password_with_valid_token_succeeds(): void
    {
        Notification::fake();
        ['user' => $user] = $this->seedContext();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email]);

        $notification = Notification::sent($user, ApiResetPasswordNotification::class)->first();
        $token = $notification->token;

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'resetPass1',
            'password_confirmation' => 'resetPass1',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('resetPass1', $user->fresh()->password));
    }

    public function test_reset_password_with_invalid_token_fails(): void
    {
        ['user' => $user] = $this->seedContext();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'resetPass1',
            'password_confirmation' => 'resetPass1',
        ])->assertStatus(422);
    }

    public function test_login_is_rate_limited(): void
    {
        ['user' => $user] = $this->seedContext();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'wrong',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ])->assertStatus(429);
    }
}

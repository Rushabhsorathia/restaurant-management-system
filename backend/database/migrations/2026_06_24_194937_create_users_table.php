<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')
                ->nullable()
                ->constrained('restaurants')
                ->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->json('settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('restaurant_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

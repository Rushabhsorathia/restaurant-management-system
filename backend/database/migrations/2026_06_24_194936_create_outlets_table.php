<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->restrictOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('type', 30)->default('dine_in');
            $table->string('gstin', 15)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pincode', 10);
            $table->boolean('is_central_kitchen')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['restaurant_id', 'code']);
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};

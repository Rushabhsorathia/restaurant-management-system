<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 20);
            $table->decimal('value', 12, 2);
            $table->string('applies_to', 20)->default('bill');
            $table->decimal('min_bill_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_configs');
    }
};

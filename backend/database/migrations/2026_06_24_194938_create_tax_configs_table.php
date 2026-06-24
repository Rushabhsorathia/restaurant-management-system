<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 20)->default('intra_state');
            $table->decimal('cgst_rate', 5, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0);
            $table->decimal('cess_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_configs');
    }
};

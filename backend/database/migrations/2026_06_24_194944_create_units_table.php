<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 10)->unique();
            $table->foreignId('base_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->decimal('conversion_factor', 12, 4)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};

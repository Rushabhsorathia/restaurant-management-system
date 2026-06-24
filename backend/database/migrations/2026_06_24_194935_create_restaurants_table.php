<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('legal_name');
            $table->string('gstin', 15)->nullable()->index();
            $table->string('pan', 10)->nullable();
            $table->string('logo_url')->nullable();
            $table->string('email');
            $table->string('phone', 20);
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pincode', 10);
            $table->string('country', 50)->default('India');
            $table->string('currency_code', 3)->default('INR');
            $table->string('timezone', 50)->default('Asia/Kolkata');
            $table->decimal('default_tax_rate', 5, 2)->nullable();
            $table->string('fssai_number', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};

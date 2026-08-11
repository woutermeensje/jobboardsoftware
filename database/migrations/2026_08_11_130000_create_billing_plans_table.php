<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('monthly_price_cents')->default(0);
            $table->string('currency', 3)->default('eur');
            $table->string('stripe_price_id')->nullable()->index();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};

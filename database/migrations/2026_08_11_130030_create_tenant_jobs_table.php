<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('title');
            $table->string('slug');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('salary_range')->nullable();
            $table->text('intro')->nullable();
            $table->longText('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_jobs');
    }
};

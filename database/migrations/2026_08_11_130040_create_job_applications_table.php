<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('tenant_job_id')->constrained('tenant_jobs')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('motivation')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('status', 32)->default('new')->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

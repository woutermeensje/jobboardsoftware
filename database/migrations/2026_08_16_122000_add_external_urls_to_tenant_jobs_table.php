<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_jobs', 'job_url')) {
                $table->string('job_url', 2048)->nullable()->after('description');
            }

            if (! Schema::hasColumn('tenant_jobs', 'company_url')) {
                $table->string('company_url', 2048)->nullable()->after('job_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_jobs', 'company_url')) {
                $table->dropColumn('company_url');
            }

            if (Schema::hasColumn('tenant_jobs', 'job_url')) {
                $table->dropColumn('job_url');
            }
        });
    }
};

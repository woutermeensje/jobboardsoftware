<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_jobs')) {
            Schema::table('tenant_jobs', function (Blueprint $table): void {
                if (! $this->hasIndex('tenant_jobs', 'tenant_jobs_public_listing_index')) {
                    $table->index(['tenant_id', 'status', 'published_at'], 'tenant_jobs_public_listing_index');
                }

                if (Schema::hasColumn('tenant_jobs', 'department') && ! $this->hasIndex('tenant_jobs', 'tenant_jobs_department_filter_index')) {
                    $table->index(['tenant_id', 'status', 'department'], 'tenant_jobs_department_filter_index');
                }

                if (Schema::hasColumn('tenant_jobs', 'employment_type') && ! $this->hasIndex('tenant_jobs', 'tenant_jobs_type_filter_index')) {
                    $table->index(['tenant_id', 'status', 'employment_type'], 'tenant_jobs_type_filter_index');
                }

                if (Schema::hasColumn('tenant_jobs', 'location') && ! $this->hasIndex('tenant_jobs', 'tenant_jobs_location_filter_index')) {
                    $table->index(['tenant_id', 'status', 'location'], 'tenant_jobs_location_filter_index');
                }

                if (Schema::hasColumn('tenant_jobs', 'tenant_company_id') && ! $this->hasIndex('tenant_jobs', 'tenant_jobs_company_filter_index')) {
                    $table->index(['tenant_id', 'status', 'tenant_company_id'], 'tenant_jobs_company_filter_index');
                }
            });
        }

        if (Schema::hasTable('tenant_packages') && ! $this->hasIndex('tenant_packages', 'tenant_packages_pricing_index')) {
            Schema::table('tenant_packages', function (Blueprint $table): void {
                $table->index(['tenant_id', 'price', 'name'], 'tenant_packages_pricing_index');
            });
        }

        if (Schema::hasTable('job_applications') && ! $this->hasIndex('job_applications', 'job_applications_job_status_index')) {
            Schema::table('job_applications', function (Blueprint $table): void {
                $table->index(['tenant_id', 'tenant_job_id', 'status'], 'job_applications_job_status_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_applications') && $this->hasIndex('job_applications', 'job_applications_job_status_index')) {
            Schema::table('job_applications', function (Blueprint $table): void {
                $table->dropIndex('job_applications_job_status_index');
            });
        }

        if (Schema::hasTable('tenant_packages') && $this->hasIndex('tenant_packages', 'tenant_packages_pricing_index')) {
            Schema::table('tenant_packages', function (Blueprint $table): void {
                $table->dropIndex('tenant_packages_pricing_index');
            });
        }

        if (Schema::hasTable('tenant_jobs')) {
            Schema::table('tenant_jobs', function (Blueprint $table): void {
                foreach ([
                    'tenant_jobs_company_filter_index',
                    'tenant_jobs_location_filter_index',
                    'tenant_jobs_type_filter_index',
                    'tenant_jobs_department_filter_index',
                    'tenant_jobs_public_listing_index',
                ] as $index) {
                    if ($this->hasIndex('tenant_jobs', $index)) {
                        $table->dropIndex($index);
                    }
                }
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return collect(Schema::getIndexes($table))
            ->contains(fn (array $existing): bool => ($existing['name'] ?? null) === $index);
    }
};

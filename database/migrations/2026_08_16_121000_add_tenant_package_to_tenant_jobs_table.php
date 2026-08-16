<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            $table->foreignId('tenant_package_id')
                ->nullable()
                ->after('tenant_company_id')
                ->constrained('tenant_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_package_id');
        });
    }
};

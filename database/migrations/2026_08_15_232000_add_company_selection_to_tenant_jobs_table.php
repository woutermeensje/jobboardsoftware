<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            $table->foreignId('tenant_company_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('tenant_companies')
                ->nullOnDelete();
            $table->string('company_logo_path')->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            $table->dropForeign(['tenant_company_id']);
            $table->dropColumn(['tenant_company_id', 'company_logo_path']);
        });
    }
};

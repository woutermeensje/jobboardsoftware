<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_companies', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_companies', 'company_url')) {
                $table->string('company_url', 2048)->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_companies', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_companies', 'company_url')) {
                $table->dropColumn('company_url');
            }
        });
    }
};

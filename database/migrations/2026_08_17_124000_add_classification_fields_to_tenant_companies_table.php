<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_companies', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('company_url');
            $table->string('organization_type')->nullable()->after('sector');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_companies', function (Blueprint $table) {
            $table->dropColumn(['sector', 'organization_type']);
        });
    }
};

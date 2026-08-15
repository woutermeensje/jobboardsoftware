<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_companies', function (Blueprint $table) {
            $table->string('organization_name')->nullable()->after('tenant_id');
            $table->string('contact_first_name')->nullable()->after('contact_name');
            $table->string('contact_last_name')->nullable()->after('contact_first_name');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_companies', function (Blueprint $table) {
            $table->dropColumn([
                'organization_name',
                'contact_first_name',
                'contact_last_name',
            ]);
        });
    }
};

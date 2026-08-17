<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_companies')) {
            return;
        }

        Schema::table('tenant_companies', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_companies', 'sector')) {
                $table->text('sector')->nullable()->change();
            }

            if (Schema::hasColumn('tenant_companies', 'organization_type')) {
                $table->text('organization_type')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_companies')) {
            return;
        }

        Schema::table('tenant_companies', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_companies', 'sector')) {
                $table->string('sector')->nullable()->change();
            }

            if (Schema::hasColumn('tenant_companies', 'organization_type')) {
                $table->string('organization_type')->nullable()->change();
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_packages')) {
            return;
        }

        Schema::table('tenant_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_packages', 'description')) {
                $table->text('description')->nullable()->after('online_days');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_packages')) {
            return;
        }

        Schema::table('tenant_packages', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_packages', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};

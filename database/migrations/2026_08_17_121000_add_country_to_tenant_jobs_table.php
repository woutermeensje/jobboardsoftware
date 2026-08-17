<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_jobs', 'country')) {
                $table->string('country', 2)->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_jobs', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};

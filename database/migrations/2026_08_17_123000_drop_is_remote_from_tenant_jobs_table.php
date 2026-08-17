<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_jobs', 'is_remote')) {
                $table->dropColumn('is_remote');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_jobs', 'is_remote')) {
                $table->boolean('is_remote')->default(false)->after('country');
            }
        });
    }
};

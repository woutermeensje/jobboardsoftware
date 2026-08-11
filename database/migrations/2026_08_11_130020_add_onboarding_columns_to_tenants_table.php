<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('billing_status', 32)->default('trial')->after('status')->index();
            $table->string('onboarding_step', 40)->default('domain')->after('billing_status');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'billing_status',
                'onboarding_step',
                'onboarding_completed_at',
            ]);
        });
    }
};

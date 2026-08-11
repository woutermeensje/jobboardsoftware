<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('billing_plan_id')->nullable()->after('company_name')->constrained('billing_plans')->nullOnDelete();
            $table->string('billing_status', 32)->default('trial')->after('billing_plan_id')->index();
            $table->string('onboarding_step', 40)->default('plan')->after('billing_status');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['billing_plan_id']);
            $table->dropColumn([
                'billing_plan_id',
                'billing_status',
                'onboarding_step',
                'onboarding_completed_at',
            ]);
        });
    }
};

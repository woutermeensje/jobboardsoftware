<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('tenant_id');
            $table->string('contact_name')->nullable()->after('company_name');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
            $table->foreignId('submitted_by_user_id')
                ->nullable()
                ->after('contact_phone')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_jobs', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_user_id']);
            $table->dropColumn([
                'company_name',
                'contact_name',
                'contact_email',
                'contact_phone',
                'submitted_by_user_id',
            ]);
        });
    }
};

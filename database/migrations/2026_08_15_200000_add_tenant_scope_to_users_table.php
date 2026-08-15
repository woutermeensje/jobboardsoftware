<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id');
            $table->dropUnique(['email']);
            $table->index(['tenant_id', 'email'], 'users_tenant_id_email_index');
            $table->index(['email', 'role'], 'users_email_role_index');
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex('users_tenant_id_email_index');
            $table->dropIndex('users_email_role_index');
            $table->unique('email');
            $table->dropColumn('tenant_id');
        });
    }
};

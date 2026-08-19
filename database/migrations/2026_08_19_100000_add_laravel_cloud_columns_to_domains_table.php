<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('cloud_domain_id')->nullable()->unique()->after('domain');
            $table->string('cloud_environment_id')->nullable()->index()->after('cloud_domain_id');
            $table->string('cloud_hostname_status', 40)->nullable()->index()->after('ssl_status');
            $table->string('cloud_ssl_status', 40)->nullable()->index()->after('cloud_hostname_status');
            $table->string('cloud_origin_status', 40)->nullable()->index()->after('cloud_ssl_status');
            $table->string('cloud_action_required', 80)->nullable()->after('cloud_origin_status');
            $table->timestamp('cloud_last_verified_at')->nullable()->after('cloud_action_required');
            $table->string('cloudflare_strategy', 20)->default('none')->after('verification_payload');
            $table->string('verification_method', 30)->default('real_time')->after('cloudflare_strategy');
            $table->string('www_redirect', 30)->nullable()->after('verification_method');
            $table->boolean('wildcard_enabled')->default(false)->after('www_redirect');
            $table->boolean('allow_downtime')->default(true)->after('wildcard_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn([
                'cloud_domain_id',
                'cloud_environment_id',
                'cloud_hostname_status',
                'cloud_ssl_status',
                'cloud_origin_status',
                'cloud_action_required',
                'cloud_last_verified_at',
                'cloudflare_strategy',
                'verification_method',
                'www_redirect',
                'wildcard_enabled',
                'allow_downtime',
            ]);
        });
    }
};

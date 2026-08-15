<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_login_redirects_to_the_custom_login_page(): void
    {
        $this->get('/workspace/login')->assertRedirect('/login');
    }

    public function test_tenant_owner_can_access_custom_workspace_pages(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
            'billing_status' => 'trial',
        ]);

        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $job = TenantJob::factory()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Sustainable Recruiter',
            'slug' => 'sustainable-recruiter',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        JobApplication::factory()->create([
            'tenant_id' => $tenant->id,
            'tenant_job_id' => $job->id,
            'name' => 'Nina Applicant',
            'email' => 'nina@example.com',
            'status' => JobApplication::STATUS_NEW,
        ]);

        foreach ([
            '/workspace',
            '/workspace/environments',
            '/workspace/environments/create',
            '/workspace/jobs',
            '/workspace/jobs/create',
            '/workspace/domains',
            '/workspace/domains/create',
            '/workspace/applications',
            '/workspace/billing',
            '/workspace/marketing',
            '/workspace/marketing/landingpagina',
            '/workspace/marketing/socials',
            '/workspace/jobs-settings',
            '/workspace/jobs-settings/sector',
            '/workspace/jobs-settings/categorie',
            '/workspace/jobs-settings/job-type',
            '/workspace/jobs-settings/organization-type',
            '/workspace/companies',
            '/workspace/companies/create',
        ] as $path) {
            $this->actingAs($owner)->get($path)->assertOk();
        }
    }

    public function test_workspace_dashboard_only_shows_owned_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);

        $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->get('/workspace')
            ->assertOk()
            ->assertSee('Acme Careers')
            ->assertDontSee('Other Careers');
    }

    public function test_old_panel_routes_are_not_available(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/filament')->assertNotFound();
        $this->actingAs($admin)->get('/filament/login')->assertNotFound();
    }

    private function tenantFor(User $owner, string $name, string $slug): Tenant
    {
        $tenant = Tenant::create([
            'id' => $slug,
            'owner_user_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_TRIAL,
            'billing_status' => 'trial',
            'onboarding_step' => 'jobs',
            'settings' => [
                'brand_name' => $name,
                'accent_color' => '#2f5f80',
                'intro' => 'View current jobs and apply directly.',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => $slug.'.jobboardsoftware.co',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
            'verified_at' => now(),
            'ssl_issued_at' => now(),
        ]);

        return $tenant;
    }
}

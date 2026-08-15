<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_dashboard_login_redirects_to_the_custom_login_page(): void
    {
        $this->get('/client/dashboard/login')->assertRedirect('/login');
        $this->get('/workspace/login')->assertRedirect('/login');
    }

    public function test_tenant_owner_can_access_custom_client_dashboard_pages(): void
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
            '/client/dashboard',
            '/client/dashboard/',
            '/client/dashboard/environments',
            '/client/dashboard/environments/create',
            '/client/dashboard/jobs',
            '/client/dashboard/jobs/create',
            '/client/dashboard/domains',
            '/client/dashboard/domains/create',
            '/client/dashboard/applications',
            '/client/dashboard/billing',
            '/client/dashboard/marketing',
            '/client/dashboard/marketing/landingpagina',
            '/client/dashboard/marketing/socials',
            '/client/dashboard/jobs-settings',
            '/client/dashboard/jobs-settings/sector',
            '/client/dashboard/jobs-settings/categorie',
            '/client/dashboard/jobs-settings/job-type',
            '/client/dashboard/jobs-settings/organization-type',
            '/client/dashboard/companies',
            '/client/dashboard/companies/create',
        ] as $path) {
            $this->actingAs($owner)
                ->get($path)
                ->assertOk()
                ->assertSee('dashboard-topbar', false)
                ->assertSee('dashboard-sidebar', false);
        }
    }

    public function test_client_dashboard_only_shows_owned_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);

        $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->get('/client/dashboard')
            ->assertOk()
            ->assertSee('Acme Careers')
            ->assertDontSee('Other Careers');
    }

    public function test_old_workspace_paths_redirect_to_client_dashboard(): void
    {
        $this->get('/workspace')->assertRedirect('/client/dashboard');
        $this->get('/workspace/environments')->assertRedirect('/client/dashboard/environments');
        $this->get('/workspace/jobs-settings/sector')->assertRedirect('/client/dashboard/jobs-settings/sector');
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

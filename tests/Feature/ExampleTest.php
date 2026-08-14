<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\BillingPlan;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_the_homepage_keeps_header_and_footer_without_welcome_content(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('JobBoardSoftware')
            ->assertDontSee('SaaS job board software')
            ->assertDontSee('Launch your own job board')
            ->assertDontSee('Laravel Developer');
    }

    public function test_the_public_navigation_targets_the_saas_product(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Product')
            ->assertSee('Features')
            ->assertSee('Connect a custom domain')
            ->assertSee('/sign-up', false)
            ->assertSee('/login', false)
            ->assertDontSee('/aanmelden/werkgever', false)
            ->assertDontSee('/aanmelden/werkzoekende', false);
    }

    public function test_public_menu_pages_are_available(): void
    {
        foreach ([
            '/job-seeker',
            '/job-alerts',
            '/newsletter',
            '/employer',
            '/post-a-job',
            '/pricing',
            '/about-us',
            '/contact',
        ] as $path) {
            $this->get($path)->assertStatus(200);
            $this->get($path.'/')->assertStatus(200);
        }
    }

    public function test_tenant_domain_renders_tenant_frontend(): void
    {
        $tenant = Tenant::create([
            'id' => 'acme',
            'name' => 'Acme Careers',
            'slug' => 'acme-careers',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => [
                'brand_name' => 'Acme Careers',
                'accent_color' => '#2f5f80',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => 'acme.test',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        $tenant->jobs()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer',
            'department' => 'Development',
            'location' => 'Amsterdam',
            'employment_type' => 'Fulltime',
            'intro' => 'Help build a growing platform.',
            'description' => 'Build modern job board software with the team.',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get('http://acme.test/')
            ->assertStatus(200)
            ->assertSee('Acme Careers')
            ->assertSee('Open roles')
            ->assertSee('Laravel Developer');
    }

    public function test_unknown_tenant_domain_returns_not_found(): void
    {
        $this->get('http://sustainablejobs.jobboardsoftware.co/')
            ->assertNotFound()
            ->assertSee('Job board not found')
            ->assertSee('sustainablejobs.jobboardsoftware.co');
    }

    public function test_saas_user_can_create_tenant_environment_with_domain(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
            'company_name' => 'Hire Labs',
        ]);

        $response = $this->actingAs($owner)->post('/dashboard/environments', [
            'name' => 'Acme Careers',
            'slug' => 'acme-careers',
            'domain' => 'https://jobs.acme.test/jobs',
        ]);

        $response->assertRedirect(route('tenant.environments.index'));

        $this->assertDatabaseHas('tenants', [
            'id' => 'acme-careers',
            'owner_user_id' => $owner->id,
            'name' => 'Acme Careers',
            'slug' => 'acme-careers',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_TRIAL,
        ]);

        $this->assertDatabaseHas('domains', [
            'tenant_id' => 'acme-careers',
            'domain' => 'jobs.acme.test',
            'is_primary' => true,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
        ]);

        $this->actingAs($owner)
            ->get('/dashboard/environments')
            ->assertStatus(200)
            ->assertSee('Acme Careers')
            ->assertSee('jobs.acme.test');
    }

    public function test_auth_pages_are_available(): void
    {
        foreach ([
            '/login',
            '/sign-up',
            '/admin/login',
        ] as $path) {
            $this->get($path)->assertStatus(200);
        }

        $this->get('/login/job-seeker')->assertRedirect('/login');
        $this->get('/login/employer')->assertRedirect('/login');
        $this->get('/sign-up/job-seeker')->assertRedirect('/sign-up');
        $this->get('/sign-up/employer')->assertRedirect('/sign-up');
        $this->get('/inloggen')->assertRedirect('/login');
        $this->get('/aanmelden')->assertRedirect('/sign-up');
        $this->get('/admin/inloggen')->assertRedirect('/admin/login');
    }

    public function test_saas_user_can_register_and_reaches_dashboard(): void
    {
        $response = $this->post('/sign-up', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'owner@example.com',
            'phone_number' => '+1 555 123 4567',
            'heard_about_us' => 'LinkedIn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.index'));
        $this->assertSame('/dashboard/onboarding', parse_url(route('onboarding.index'), PHP_URL_PATH));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'owner@example.com',
            'phone_number' => '+1 555 123 4567',
            'heard_about_us' => 'LinkedIn',
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->get('/dashboard/onboarding')
            ->assertStatus(200)
            ->assertSee('Launch your job board')
            ->assertSee('Choose package');
    }

    public function test_saas_user_can_login_and_reaches_dashboard(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner-login@example.com',
            'password' => 'password123',
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $response = $this->post('/login', [
            'email' => 'owner-login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('tenant.owner.dashboard'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_dashboard_routes_are_role_protected(): void
    {
        $jobSeeker = User::factory()->create([
            'role' => User::ROLE_WERKZOEKENDE,
        ]);

        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->get('/dashboard')->assertRedirect(route('login.choice'));
        $this->actingAs($jobSeeker)->get('/dashboard')->assertForbidden();
        $this->actingAs($owner)->get('/dashboard')->assertStatus(200);
    }

    public function test_old_dashboard_paths_redirect_to_the_saas_dashboard(): void
    {
        $this->get('/werkzoekende/dashboard')->assertRedirect('/dashboard');
        $this->get('/werkgever/dashboard')->assertRedirect('/dashboard');
        $this->get('/dashboard/werkgever')->assertRedirect('/dashboard');
        $this->get('/dashboard/werkgever/omgeving')->assertRedirect('/dashboard/environments');
        $this->get('/dashboard/omgeving')->assertRedirect('/dashboard/environments');
    }

    public function test_admin_can_login_and_reaches_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Wouter',
            'email' => 'wouter@inhuren.com',
            'password' => 'AdminPassword123!',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wouter@inhuren.com',
            'password' => 'AdminPassword123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/dashboard')->assertStatus(200)->assertSee('Platform management');
    }

    public function test_saas_user_can_select_a_billing_plan(): void
    {
        $plan = BillingPlan::create([
            'key' => Tenant::PLAN_STARTER,
            'name' => 'Starter',
            'description' => 'Starter package',
            'monthly_price_cents' => 4900,
            'currency' => 'eur',
            'features' => ['1 jobboard'],
            'limits' => ['tenants' => 1],
            'is_active' => true,
        ]);

        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->actingAs($owner)
            ->post('/dashboard/billing/plan', ['plan_key' => Tenant::PLAN_STARTER])
            ->assertRedirect(route('onboarding.index'));

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'billing_plan_id' => $plan->id,
            'billing_status' => 'trial',
        ]);
    }

    public function test_saas_user_can_manage_jobs_for_a_tenant(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $tenant = Tenant::create([
            'id' => 'hire-labs',
            'owner_user_id' => $owner->id,
            'name' => 'Hire Labs',
            'slug' => 'hire-labs',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner)
            ->post(route('tenant.jobs.store', $tenant), [
                'title' => 'PHP Developer',
                'department' => 'Development',
                'location' => 'Amsterdam',
                'employment_type' => 'Fulltime',
                'intro' => 'Build with us.',
                'description' => 'A detailed job description.',
                'status' => TenantJob::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('tenant.jobs.index', $tenant));

        $this->assertDatabaseHas('tenant_jobs', [
            'tenant_id' => 'hire-labs',
            'title' => 'PHP Developer',
            'slug' => 'php-developer',
            'status' => TenantJob::STATUS_PUBLISHED,
        ]);

        $this->actingAs($owner)
            ->get(route('tenant.jobs.index', $tenant))
            ->assertStatus(200)
            ->assertSee('PHP Developer');
    }

    public function test_candidate_can_apply_on_tenant_frontend(): void
    {
        $tenant = Tenant::create([
            'id' => 'front',
            'name' => 'Frontend Careers',
            'slug' => 'frontend-careers',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => [
                'brand_name' => 'Frontend Careers',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => 'front.test',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        $job = $tenant->jobs()->create([
            'title' => 'Frontend Developer',
            'slug' => 'frontend-developer',
            'department' => 'Development',
            'location' => 'Utrecht',
            'employment_type' => 'Fulltime',
            'description' => 'Create polished interfaces.',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->post('http://front.test/jobs/frontend-developer/apply', [
            'name' => 'Sanne Applicant',
            'email' => 'sanne@example.com',
            'phone' => '0612345678',
            'motivation' => 'I am excited about this role.',
        ])->assertRedirect('http://front.test/jobs/frontend-developer');

        $this->assertDatabaseHas('job_applications', [
            'tenant_id' => 'front',
            'tenant_job_id' => $job->id,
            'email' => 'sanne@example.com',
            'status' => JobApplication::STATUS_NEW,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Domain;
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
            ->assertSee('rn-header', false)
            ->assertSee('si-footer', false)
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

        $tenant->jobs()->create([
            'title' => 'Growth Marketer',
            'slug' => 'growth-marketer',
            'department' => 'Marketing',
            'location' => 'Rotterdam',
            'employment_type' => 'Parttime',
            'intro' => 'Grow the audience for this job board.',
            'description' => 'Build campaigns with the team.',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get('http://acme.test/')
            ->assertStatus(200)
            ->assertSee('Acme Careers')
            ->assertSee('tenant-header', false)
            ->assertSee('tenant-mobile-nav', false)
            ->assertSee('Post job')
            ->assertSee('Login')
            ->assertSee('Search all jobs')
            ->assertDontSee('Open roles')
            ->assertDontSee('Plan: Starter')
            ->assertSee('tenant-job-filters', false)
            ->assertSee('tenant-jobs-sidebar', false)
            ->assertSee('tenant-sidebar-filter-count', false)
            ->assertSee('Category')
            ->assertSee('Job type')
            ->assertSee('Laravel Developer')
            ->assertSee('Growth Marketer')
            ->assertSee('Amsterdam')
            ->assertSee('Rotterdam');

        $this->get('http://acme.test/?department%5B%5D=Development')
            ->assertStatus(200)
            ->assertSee('Laravel Developer')
            ->assertDontSee('Growth Marketer');

        $this->get('http://acme.test/?location=Rotterdam&employment_type%5B%5D=Parttime')
            ->assertStatus(200)
            ->assertSee('Growth Marketer')
            ->assertDontSee('Laravel Developer');
    }

    public function test_unknown_tenant_domain_returns_not_found(): void
    {
        $this->get('http://sustainablejobs.jobboardsoftware.co/')
            ->assertNotFound()
            ->assertSee('Job board not found')
            ->assertSee('sustainablejobs.jobboardsoftware.co');
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

        $this->get('/login/jobseeker')->assertRedirect('/login');
        $this->get('/login/job-seeker')->assertRedirect('/login');
        $this->get('/login/employer')->assertRedirect('/login');
        $this->get('/sign-up/jobseeker')->assertRedirect('/sign-up');
        $this->get('/sign-up/job-seeker')->assertRedirect('/sign-up');
        $this->get('/sign-up/employer')->assertRedirect('/sign-up');
        $this->get('/inloggen')->assertRedirect('/login');
        $this->get('/aanmelden')->assertRedirect('/sign-up');
        $this->get('/admin/inloggen')->assertRedirect('/admin/login');
    }

    public function test_tenant_auth_pages_create_tenant_scoped_jobseeker_and_employer_accounts(): void
    {
        $tenant = $this->tenantWithDomain('tenant-auth', 'tenant-auth.test');

        $this->get('http://tenant-auth.test/login')
            ->assertOk()
            ->assertSee('Job seeker')
            ->assertSee('Employer')
            ->assertSee('Create account')
            ->assertDontSee('Admin');

        $this->get('http://tenant-auth.test/sign-up')
            ->assertOk()
            ->assertSee('Job seeker')
            ->assertSee('Employer')
            ->assertSee('I already have an account');

        $this->get('http://tenant-auth.test/login/jobseeker')
            ->assertOk()
            ->assertSee('Log in as a job seeker');

        $this->get('http://tenant-auth.test/login/employer')
            ->assertOk()
            ->assertSee('Log in as an employer');

        $this->get('http://tenant-auth.test/sign-up/jobseeker')
            ->assertOk()
            ->assertSee('Create a job seeker account');

        $this->get('http://tenant-auth.test/sign-up/employer')
            ->assertOk()
            ->assertSee('Create an employer account')
            ->assertSee('Company name');

        $this->post('http://tenant-auth.test/sign-up/jobseeker', [
            'first_name' => 'Jane',
            'last_name' => 'Candidate',
            'email' => 'jane@example.com',
            'phone_number' => '+1 555 111 2222',
            'heard_about_us' => 'Search',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('http://tenant-auth.test/jobseeker/dashboard');

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
            'role' => User::ROLE_JOBSEEKER,
        ]);

        $this->post('http://tenant-auth.test/logout')->assertRedirect('http://tenant-auth.test/login');

        $this->post('http://tenant-auth.test/sign-up/employer', [
            'first_name' => 'Evan',
            'last_name' => 'Employer',
            'email' => 'evan@example.com',
            'phone_number' => '+1 555 333 4444',
            'company_name' => 'Tenant Hiring Co.',
            'heard_about_us' => 'LinkedIn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('http://tenant-auth.test/employer/dashboard');

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'name' => 'Evan Employer',
            'email' => 'evan@example.com',
            'company_name' => 'Tenant Hiring Co.',
            'role' => User::ROLE_EMPLOYER,
        ]);
    }

    public function test_tenant_accounts_are_scoped_by_tenant_domain(): void
    {
        $acme = $this->tenantWithDomain('acme-auth', 'acme-auth.test');
        $other = $this->tenantWithDomain('other-auth', 'other-auth.test');

        $this->post('http://acme-auth.test/sign-up/jobseeker', [
            'first_name' => 'Sam',
            'last_name' => 'Scoped',
            'email' => 'same@example.com',
            'phone_number' => '+1 555 111 1111',
            'heard_about_us' => 'Search',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('http://acme-auth.test/jobseeker/dashboard');

        $this->post('http://acme-auth.test/logout')->assertRedirect('http://acme-auth.test/login');

        $this->post('http://other-auth.test/sign-up/jobseeker', [
            'first_name' => 'Sam',
            'last_name' => 'Other',
            'email' => 'same@example.com',
            'phone_number' => '+1 555 222 2222',
            'heard_about_us' => 'Search',
            'password' => 'different123',
            'password_confirmation' => 'different123',
        ])->assertRedirect('http://other-auth.test/jobseeker/dashboard');

        $this->assertDatabaseHas('users', [
            'tenant_id' => $acme->id,
            'email' => 'same@example.com',
            'role' => User::ROLE_JOBSEEKER,
        ]);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $other->id,
            'email' => 'same@example.com',
            'role' => User::ROLE_JOBSEEKER,
        ]);

        $this->post('http://other-auth.test/logout')->assertRedirect('http://other-auth.test/login');

        $this->post('http://acme-auth.test/login/jobseeker', [
            'email' => 'same@example.com',
            'password' => 'different123',
        ])->assertSessionHasErrors('email');

        $this->post('http://acme-auth.test/login/jobseeker', [
            'email' => 'same@example.com',
            'password' => 'password123',
        ])->assertRedirect('http://acme-auth.test/jobseeker/dashboard');

        $this->assertAuthenticated();
        $this->assertSame($acme->id, auth()->user()->tenant_id);
    }

    public function test_tenant_jobseeker_and_employer_dashboards_use_the_dashboard_shell(): void
    {
        $tenant = $this->tenantWithDomain('dash-auth', 'dash-auth.test');
        $job = $tenant->jobs()->create([
            'title' => 'Product Designer',
            'slug' => 'product-designer',
            'department' => 'Design',
            'location' => 'Remote',
            'employment_type' => 'Fulltime',
            'description' => 'Design polished product experiences.',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $tenant->jobs()->create([
            'title' => 'Draft Recruiter',
            'slug' => 'draft-recruiter',
            'department' => 'People',
            'location' => 'Amsterdam',
            'employment_type' => 'Parttime',
            'description' => 'Draft role.',
            'status' => TenantJob::STATUS_DRAFT,
        ]);

        JobApplication::create([
            'tenant_id' => $tenant->id,
            'tenant_job_id' => $job->id,
            'name' => 'Jane Candidate',
            'email' => 'candidate@example.com',
            'status' => JobApplication::STATUS_REVIEWED,
        ]);

        JobApplication::create([
            'tenant_id' => $tenant->id,
            'tenant_job_id' => $job->id,
            'name' => 'Other Candidate',
            'email' => 'other@example.com',
            'status' => JobApplication::STATUS_NEW,
        ]);

        $jobseeker = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Jane Candidate',
            'email' => 'candidate@example.com',
            'role' => User::ROLE_JOBSEEKER,
        ]);

        $employer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Evan Employer',
            'email' => 'employer@example.com',
            'company_name' => 'Dash Hiring Co.',
            'role' => User::ROLE_EMPLOYER,
        ]);

        $this->get('http://dash-auth.test/jobseeker/dashboard')
            ->assertRedirect('http://dash-auth.test/login');

        $this->actingAs($jobseeker)
            ->get('http://dash-auth.test/jobseeker/dashboard')
            ->assertOk()
            ->assertSee('dashboard-topbar', false)
            ->assertSee('dashboard-sidebar', false)
            ->assertSee('Job seeker dashboard')
            ->assertSee('Recommended jobs')
            ->assertSee('Product Designer')
            ->assertSee('Jane Candidate')
            ->assertDontSee('Other Candidate');

        $this->actingAs($jobseeker)
            ->get('http://dash-auth.test/employer/dashboard')
            ->assertForbidden();

        $this->actingAs($employer)
            ->get('http://dash-auth.test/employer/dashboard')
            ->assertOk()
            ->assertSee('dashboard-topbar', false)
            ->assertSee('dashboard-sidebar', false)
            ->assertSee('Employer dashboard')
            ->assertSee('Jobs')
            ->assertSee('Applications')
            ->assertSee('Product Designer')
            ->assertSee('Other Candidate');

        $this->actingAs($employer)
            ->get('http://dash-auth.test/jobseeker/dashboard')
            ->assertForbidden();
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

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'owner@example.com',
            'phone_number' => '+1 555 123 4567',
            'heard_about_us' => 'LinkedIn',
            'role' => User::ROLE_TENANT_OWNER,
            'tenant_id' => null,
        ]);
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

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_dashboard_path_is_no_longer_available(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->actingAs($owner)->get('/dashboard')->assertNotFound();
    }

    public function test_old_dashboard_paths_redirect_to_the_client_dashboard(): void
    {
        $this->get('/werkzoekende/dashboard')->assertRedirect('/client/dashboard');
        $this->get('/werkgever/dashboard')->assertRedirect('/client/dashboard');
        $this->get('/dashboard/werkgever')->assertRedirect('/client/dashboard');
        $this->get('/dashboard/werkgever/omgeving')->assertRedirect('/client/dashboard/environments');
        $this->get('/dashboard/omgeving')->assertRedirect('/client/dashboard/environments');
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

    private function tenantWithDomain(string $id, string $domain): Tenant
    {
        $tenant = Tenant::create([
            'id' => $id,
            'name' => str($id)->headline()->append(' Careers')->toString(),
            'slug' => $id,
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => [
                'brand_name' => str($id)->headline()->append(' Careers')->toString(),
            ],
        ]);

        $tenant->domains()->create([
            'domain' => $domain,
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        return $tenant;
    }
}

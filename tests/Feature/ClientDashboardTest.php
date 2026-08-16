<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\TenantJob;
use App\Models\TenantPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            '/client/dashboard/packages',
        ] as $path) {
            $this->actingAs($owner)
                ->get($path)
                ->assertOk()
                ->assertSee('dashboard-topbar', false)
                ->assertSee('dashboard-sidebar', false)
                ->assertDontSee('dashboard-topbar__heading', false);
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
            ->assertDontSee('Workspace sections')
            ->assertDontSee('dash-stats', false)
            ->assertDontSee('Other Careers');
    }

    public function test_client_dashboard_sidebar_shows_sub_navigation(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);

        $this->actingAs($owner)
            ->get('/client/dashboard')
            ->assertOk()
            ->assertSee('Create environment')
            ->assertSee('Create job')
            ->assertSee('Add domain')
            ->assertSee('Landing pages')
            ->assertSee('Social channels')
            ->assertSee('Sectors')
            ->assertSee('Categories')
            ->assertSee('Job types')
            ->assertSee('Organization types')
            ->assertSee('Create company')
            ->assertSee('My packages')
            ->assertSee('/client/dashboard/jobs-settings/job-type', false);
    }

    public function test_client_dashboard_form_pages_use_the_split_layout(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        foreach ([
            '/client/dashboard/domains',
            '/client/dashboard/jobs/create',
            '/client/dashboard/marketing/landingpagina',
            '/client/dashboard/jobs-settings/categorie',
            '/client/dashboard/jobs-settings/job-type',
            '/client/dashboard/companies/create',
            '/client/dashboard/packages',
        ] as $path) {
            $this->actingAs($owner)
                ->get($path)
                ->assertOk()
                ->assertSee('dash-form-layout', false)
                ->assertSee('dash-form-layout__aside', false);
        }
    }

    public function test_tenant_scoped_employer_cannot_access_the_application_wide_client_dashboard(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $employer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_EMPLOYER,
        ]);

        $this->actingAs($employer)
            ->get('/client/dashboard')
            ->assertForbidden();
    }

    public function test_tenant_owner_can_create_job_from_the_client_dashboard(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create([
            'name' => 'Olivia Owner',
            'email' => 'owner@example.com',
            'role' => User::ROLE_TENANT_OWNER,
        ]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $tenant->forceFill([
            'settings' => [
                'custom_job_types' => ['Volunteer'],
            ],
        ])->save();

        $company = TenantCompany::query()->create([
            'tenant_id' => $tenant->id,
            'organization_name' => 'Acme Group',
            'name' => 'Acme Hiring',
            'slug' => 'acme-hiring',
            'contact_name' => 'Maya Collins',
            'contact_email' => 'maya@example.com',
            'contact_phone' => '+31 20 123 4567',
            'logo_path' => 'company-logos/acme.svg',
        ]);

        $this->actingAs($owner)
            ->get('/client/dashboard/jobs/create')
            ->assertOk()
            ->assertSee('dash-form-layout', false)
            ->assertSee('Create job')
            ->assertSee('Job details')
            ->assertSee('Company logo')
            ->assertSee('Select company')
            ->assertSee('Acme Hiring')
            ->assertSee('Volunteer')
            ->assertSee('data-quill-field', false)
            ->assertSee('cdn.jsdelivr.net/npm/quill@2/dist/quill.js', false);

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs', [
                'tenant_id' => $tenant->id,
                'tenant_company_id' => $company->id,
                'company_logo' => UploadedFile::fake()->create('job-logo.png', 24, 'image/png'),
                'title' => 'Community Lead',
                'category' => 'Community',
                'location' => 'Amsterdam',
                'employment_type' => 'Volunteer',
                'salary_range' => 'Upon request',
                'intro' => '<p>Lead a <strong>community</strong>.</p>',
                'description' => '<p>Own events and candidate engagement.</p><script>alert("xss")</script>',
                'status' => TenantJob::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('client.jobs.create'))
            ->assertSessionHas('status', 'Job created.');

        $job = TenantJob::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', 'community-lead')
            ->firstOrFail();

        $this->assertSame($company->id, $job->tenant_company_id);
        $this->assertSame('Acme Hiring', $job->company_name);
        $this->assertSame('Volunteer', $job->employment_type);
        $this->assertSame(TenantJob::STATUS_PUBLISHED, $job->status);
        $this->assertNotNull($job->published_at);
        $this->assertSame('Maya Collins', $job->contact_name);
        $this->assertSame('maya@example.com', $job->contact_email);
        $this->assertSame('<p>Lead a <strong>community</strong>.</p>', $job->intro);
        $this->assertStringNotContainsString('script', $job->description);
        $this->assertNotNull($job->company_logo_path);
        Storage::disk('public')->assertExists($job->company_logo_path);
        Storage::disk('public')->delete($job->company_logo_path);
    }

    public function test_tenant_owner_can_create_company_with_logo(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $this->actingAs($owner)
            ->get('/client/dashboard/companies/create')
            ->assertOk()
            ->assertSee('dash-form-layout', false)
            ->assertSee('Create company')
            ->assertSee('Organization name')
            ->assertSee('Company name (for job posts)')
            ->assertSee('Company logo')
            ->assertSee('Contact details')
            ->assertSee('First name')
            ->assertSee('Last name')
            ->assertSee('Email address')
            ->assertSee('Phone number')
            ->assertSee('Choose file')
            ->assertSee('No file selected')
            ->assertSee('Company profile')
            ->assertSee('Upload a PNG, JPG, WebP or SVG logo.');

        $this->actingAs($owner)
            ->post('/client/dashboard/companies', [
                'tenant_id' => $tenant->id,
                'organization_name' => 'Northwind Group',
                'name' => 'Northwind Hiring',
                'contact_first_name' => 'Maya',
                'contact_last_name' => 'Collins',
                'contact_email' => 'maya@example.com',
                'contact_phone' => '+31 20 123 4567',
                'description' => 'Hiring team for seasonal roles.',
                'logo' => UploadedFile::fake()->create('northwind-logo.png', 32, 'image/png'),
            ])
            ->assertRedirect(route('client.companies.index'))
            ->assertSessionHas('status', 'Company created.');

        $company = TenantCompany::query()->firstOrFail();

        $this->assertSame($tenant->id, $company->tenant_id);
        $this->assertSame('Northwind Group', $company->organization_name);
        $this->assertSame('Northwind Hiring', $company->name);
        $this->assertSame('northwind-hiring', $company->slug);
        $this->assertSame('Maya', $company->contact_first_name);
        $this->assertSame('Collins', $company->contact_last_name);
        $this->assertSame('Maya Collins', $company->contact_name);
        $this->assertNotNull($company->logo_path);
        Storage::disk('public')->assertExists($company->logo_path);

        $this->actingAs($owner)
            ->get('/client/dashboard/companies')
            ->assertOk()
            ->assertSee('Northwind Hiring')
            ->assertSee('Northwind Group')
            ->assertSee('Maya Collins')
            ->assertSee('storage/company-logos', false);
    }

    public function test_tenant_owner_cannot_create_company_for_unowned_environment(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->post('/client/dashboard/companies', [
                'tenant_id' => $otherTenant->id,
                'organization_name' => 'Blocked Group',
                'name' => 'Blocked Company',
                'logo' => UploadedFile::fake()->create('blocked-logo.png', 32, 'image/png'),
            ])
            ->assertSessionHasErrors('tenant_id');

        $this->assertDatabaseMissing('tenant_companies', [
            'tenant_id' => $otherTenant->id,
            'name' => 'Blocked Company',
        ]);
        Storage::disk('public')->assertMissing('company-logos/blocked-logo.png');
    }

    public function test_tenant_owner_can_connect_a_custom_domain(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $this->actingAs($owner)
            ->post('/client/dashboard/domains', [
                'tenant_id' => $tenant->id,
                'domain' => 'https://Careers.Example.com/jobs',
                'is_primary' => '1',
            ])
            ->assertRedirect(route('client.domains.index'))
            ->assertSessionHas('status', 'Domain connected. Add the DNS records below to complete verification.');

        $this->assertDatabaseHas('domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'careers.example.com',
            'is_primary' => true,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
        ]);

        $this->assertDatabaseHas('domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'acme-careers.jobboardsoftware.co',
            'is_primary' => false,
        ]);

        $domain = Domain::where('domain', 'careers.example.com')->firstOrFail();

        $this->assertNotNull($domain->verification_token);
        $this->assertSame('jobboardsoftware.co', $domain->verification_payload['value']);
        $this->assertSame('_jobboardsoftware-verification.careers.example.com', $domain->verification_payload['txt_name']);

        $this->actingAs($owner)
            ->get('/client/dashboard/domains')
            ->assertOk()
            ->assertSee('careers.example.com')
            ->assertSee('jobboardsoftware-site-verification=');
    }

    public function test_tenant_owner_cannot_connect_a_domain_to_another_users_environment(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->post('/client/dashboard/domains', [
                'tenant_id' => $otherTenant->id,
                'domain' => 'jobs.example.com',
            ])
            ->assertSessionHasErrors('tenant_id');

        $this->assertDatabaseMissing('domains', [
            'tenant_id' => $otherTenant->id,
            'domain' => 'jobs.example.com',
        ]);
    }

    public function test_tenant_owner_can_manage_job_types_for_owned_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $this->actingAs($owner)
            ->get('/client/dashboard/jobs-settings/job-type')
            ->assertOk()
            ->assertSee('Job types')
            ->assertSee('Part time')
            ->assertSee('Full time')
            ->assertSee('Freelance')
            ->assertSee('Temporary')
            ->assertSee('Internship')
            ->assertSee('No custom types');

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs-settings/job-type', [
                'tenant_id' => $tenant->id,
                'name' => 'Volunteer',
            ])
            ->assertRedirect(route('client.jobs-settings.job-type'))
            ->assertSessionHas('status', 'Job type added.');

        $tenant->refresh();

        $this->assertSame(['Volunteer'], $tenant->settings['custom_job_types'] ?? []);

        $this->actingAs($owner)
            ->get('/client/dashboard/jobs-settings/job-type')
            ->assertOk()
            ->assertSee('Volunteer');
    }

    public function test_tenant_owner_can_manage_packages_for_owned_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $this->actingAs($owner)
            ->get('/client/dashboard/packages')
            ->assertOk()
            ->assertSee('My packages')
            ->assertSee('Package name')
            ->assertSee('Price')
            ->assertSee('Currency')
            ->assertSee('Days online')
            ->assertSee('No packages yet');

        $this->actingAs($owner)
            ->post('/client/dashboard/packages', [
                'tenant_id' => $tenant->id,
                'name' => 'Featured job',
                'price' => '149.00',
                'currency' => 'eur',
                'online_days' => 45,
            ])
            ->assertRedirect(route('client.packages.index'))
            ->assertSessionHas('status', 'Package added.');

        $package = TenantPackage::query()->firstOrFail();

        $this->assertSame($tenant->id, $package->tenant_id);
        $this->assertSame('Featured job', $package->name);
        $this->assertSame('149.00', $package->price);
        $this->assertSame('EUR', $package->currency);
        $this->assertSame(45, $package->online_days);

        $this->actingAs($owner)
            ->get('/client/dashboard/packages')
            ->assertOk()
            ->assertSee('Featured job')
            ->assertSee('EUR 149.00')
            ->assertSee('45');
    }

    public function test_tenant_owner_cannot_create_package_for_unowned_environment(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->post('/client/dashboard/packages', [
                'tenant_id' => $otherTenant->id,
                'name' => 'Blocked package',
                'price' => '49.00',
                'currency' => 'EUR',
                'online_days' => 14,
            ])
            ->assertSessionHasErrors('tenant_id');

        $this->assertDatabaseMissing('tenant_packages', [
            'tenant_id' => $otherTenant->id,
            'name' => 'Blocked package',
        ]);
    }

    public function test_tenant_owner_cannot_add_duplicate_or_unowned_job_types(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs-settings/job-type', [
                'tenant_id' => $tenant->id,
                'name' => 'full time',
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs-settings/job-type', [
                'tenant_id' => $otherTenant->id,
                'name' => 'Volunteer',
            ])
            ->assertSessionHasErrors('tenant_id');

        $tenant->refresh();
        $otherTenant->refresh();

        $this->assertArrayNotHasKey('custom_job_types', $tenant->settings ?? []);
        $this->assertArrayNotHasKey('custom_job_types', $otherTenant->settings ?? []);
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

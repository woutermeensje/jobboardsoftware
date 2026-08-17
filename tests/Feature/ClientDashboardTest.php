<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\TenantJob;
use App\Models\TenantPackage;
use App\Models\User;
use App\Support\PublicUploadStorage;
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
        $company = TenantCompany::query()->create([
            'tenant_id' => $tenant->id,
            'organization_name' => 'Acme Group',
            'name' => 'Acme Hiring',
            'slug' => 'acme-hiring',
        ]);
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
            '/client/dashboard/jobs/'.$job->id.'/edit',
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
            '/client/dashboard/companies/'.$company->id.'/edit',
            '/client/dashboard/packages',
            '/client/dashboard/packages/create',
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
            ->assertSee('Add packages')
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
            '/client/dashboard/jobs-settings/sector',
            '/client/dashboard/jobs-settings/categorie',
            '/client/dashboard/jobs-settings/job-type',
            '/client/dashboard/jobs-settings/organization-type',
            '/client/dashboard/companies/create',
            '/client/dashboard/packages/create',
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
        Storage::fake(PublicUploadStorage::diskName());

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
            ->assertDontSee('Publishing')
            ->assertSee('Job details')
            ->assertSee('Company information')
            ->assertSee('Company website URL')
            ->assertSee('Add a homepage, about page, or another relevant company page for this company.')
            ->assertSee('Vacancy URL')
            ->assertSee('Add the link to this vacancy on the client website.')
            ->assertSee('Select company')
            ->assertSee('Search company')
            ->assertSee('Acme Hiring')
            ->assertSee('Volunteer')
            ->assertSee('First name')
            ->assertSee('Last name')
            ->assertSee('Publish')
            ->assertSee('Save as draft')
            ->assertDontSee('Company logo')
            ->assertSee('data-quill-field', false)
            ->assertSee('cdn.jsdelivr.net/npm/quill@2/dist/quill.js', false)
            ->assertDontSee('placeholder=', false);

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs', [
                'tenant_id' => $tenant->id,
                'tenant_company_id' => $company->id,
                'title' => 'Community Lead',
                'job_url' => 'jobs.example.com/community-lead',
                'company_url' => 'https://acme.example.com/about',
                'location' => 'Amsterdam',
                'employment_type' => 'Volunteer',
                'description' => '<p>Own events and candidate engagement.</p><script>alert("xss")</script>',
                'contact_first_name' => 'Maya',
                'contact_last_name' => 'Collins',
                'contact_email' => 'maya@example.com',
                'contact_phone' => '+31 20 123 4567',
                'status' => TenantJob::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('client.jobs.index'))
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
        $this->assertSame('https://jobs.example.com/community-lead', $job->job_url);
        $this->assertSame('https://acme.example.com/about', $job->company_url);
        $this->assertStringNotContainsString('script', $job->description);
        $this->assertSame('company-logos/acme.svg', $job->company_logo_path);

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs', [
                'tenant_id' => $tenant->id,
                'tenant_company_id' => $company->id,
                'title' => 'Community Coordinator',
                'location' => 'Remote',
                'employment_type' => 'Volunteer',
                'description' => '<p>Coordinate the community calendar.</p>',
                'contact_first_name' => 'Maya',
                'contact_last_name' => 'Collins',
                'contact_email' => 'maya@example.com',
                'status' => TenantJob::STATUS_DRAFT,
            ])
            ->assertRedirect(route('client.jobs.index'))
            ->assertSessionHas('status', 'Job created.');

        $this->actingAs($owner)
            ->get('/client/dashboard/jobs')
            ->assertOk()
            ->assertSee('/client/dashboard/jobs/'.$job->id.'/edit', false)
            ->assertSee('Community Lead')
            ->assertSee('Community Coordinator')
            ->assertSee('Published')
            ->assertSee('Draft');
    }

    public function test_tenant_owner_can_edit_job_from_the_client_dashboard(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $tenant->forceFill([
            'settings' => [
                'custom_job_types' => ['Volunteer'],
            ],
        ])->save();

        $employer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_EMPLOYER,
        ]);
        $originalCompany = TenantCompany::query()->create([
            'tenant_id' => $tenant->id,
            'organization_name' => 'Acme Group',
            'name' => 'Acme Hiring',
            'slug' => 'acme-hiring',
            'logo_path' => 'company-logos/acme.svg',
        ]);
        $newCompany = TenantCompany::query()->create([
            'tenant_id' => $tenant->id,
            'organization_name' => 'Northwind Group',
            'name' => 'Northwind Hiring',
            'slug' => 'northwind-hiring',
            'logo_path' => 'company-logos/northwind.svg',
        ]);
        $job = TenantJob::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_company_id' => $originalCompany->id,
            'company_name' => 'Acme Hiring',
            'company_logo_path' => 'company-logos/acme.svg',
            'contact_name' => 'Maya Collins',
            'contact_email' => 'maya@example.com',
            'contact_phone' => '+31 20 123 4567',
            'submitted_by_user_id' => $employer->id,
            'title' => 'Community Lead',
            'slug' => 'community-lead',
            'location' => 'Amsterdam',
            'employment_type' => 'Full time',
            'description' => '<p>Original description.</p>',
            'job_url' => 'https://jobs.example.com/community-lead',
            'company_url' => 'https://acme.example.com/about',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($owner)
            ->get('/client/dashboard/jobs/'.$job->id.'/edit')
            ->assertOk()
            ->assertSee('Edit job')
            ->assertSee('Community Lead')
            ->assertSee('Acme Hiring')
            ->assertSee('Northwind Hiring')
            ->assertSee('value="Maya"', false)
            ->assertSee('value="Collins"', false)
            ->assertDontSee('placeholder=', false);

        $this->actingAs($owner)
            ->patch('/client/dashboard/jobs/'.$job->id, [
                'tenant_id' => $tenant->id,
                'tenant_company_id' => $newCompany->id,
                'title' => 'Updated Community Lead',
                'job_url' => 'jobs.example.com/updated-community-lead',
                'company_url' => '',
                'location' => 'Remote GMT+1',
                'employment_type' => 'Volunteer',
                'description' => '<p>Updated <strong>description</strong>.</p><script>alert("xss")</script>',
                'contact_first_name' => 'Nina',
                'contact_last_name' => 'Owner',
                'contact_email' => 'nina@example.com',
                'contact_phone' => '+31 20 987 6543',
                'status' => TenantJob::STATUS_DRAFT,
            ])
            ->assertRedirect(route('client.jobs.edit', $job))
            ->assertSessionHas('status', 'Job updated.');

        $job->refresh();

        $this->assertSame($tenant->id, $job->tenant_id);
        $this->assertSame($newCompany->id, $job->tenant_company_id);
        $this->assertSame('Northwind Hiring', $job->company_name);
        $this->assertSame('company-logos/northwind.svg', $job->company_logo_path);
        $this->assertSame('Updated Community Lead', $job->title);
        $this->assertSame('updated-community-lead', $job->slug);
        $this->assertSame('Remote GMT+1', $job->location);
        $this->assertSame('Volunteer', $job->employment_type);
        $this->assertSame('<p>Updated <strong>description</strong>.</p>', $job->description);
        $this->assertSame('https://jobs.example.com/updated-community-lead', $job->job_url);
        $this->assertNull($job->company_url);
        $this->assertSame('Nina Owner', $job->contact_name);
        $this->assertSame('nina@example.com', $job->contact_email);
        $this->assertSame('+31 20 987 6543', $job->contact_phone);
        $this->assertSame(TenantJob::STATUS_DRAFT, $job->status);
        $this->assertNull($job->published_at);
        $this->assertSame($employer->id, $job->submitted_by_user_id);
    }

    public function test_public_post_job_submission_appears_as_draft_in_client_jobs_overview(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Public Board', 'public-board');
        $tenant->domains()->create([
            'domain' => 'public-board.test',
            'is_primary' => false,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
            'verified_at' => now(),
            'ssl_issued_at' => now(),
        ]);

        $this->post('http://public-board.test/post-a-job', [
            'title' => 'Public Draft Role',
            'contact_first_name' => 'Casey',
            'contact_last_name' => 'Contact',
            'contact_email' => 'casey@example.com',
            'contact_phone' => '+1 555 444 5555',
            'location' => 'Remote',
            'employment_type' => ['Full time'],
            'description' => '<p>Submitted from the public post-a-job form.</p>',
        ])
            ->assertRedirect('http://public-board.test/post-a-job')
            ->assertSessionHas('status', 'Your job has been submitted as a draft.');

        $job = TenantJob::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', 'public-draft-role')
            ->firstOrFail();

        $this->assertSame(TenantJob::STATUS_DRAFT, $job->status);
        $this->assertNull($job->published_at);

        $this->actingAs($owner)
            ->get('https://jobboardsoftware.co/client/dashboard/jobs')
            ->assertOk()
            ->assertSee('/client/dashboard/jobs/'.$job->id.'/edit', false)
            ->assertSee('Public Draft Role')
            ->assertSee('Public Board')
            ->assertSee('Draft');

        $this->actingAs($owner)
            ->get('https://jobboardsoftware.co/client/dashboard/jobs/'.$job->id.'/edit')
            ->assertOk()
            ->assertSee('Edit job')
            ->assertSee('Company name')
            ->assertSee('Public Board')
            ->assertSee('Public Draft Role');

        $this->actingAs($owner)
            ->patch('https://jobboardsoftware.co/client/dashboard/jobs/'.$job->id, [
                'tenant_id' => $tenant->id,
                'company_name' => 'Public Board',
                'title' => 'Published Public Role',
                'location' => 'Remote',
                'employment_type' => 'Full time',
                'description' => '<p>Ready to publish.</p>',
                'contact_first_name' => 'Casey',
                'contact_last_name' => 'Contact',
                'contact_email' => 'casey@example.com',
                'contact_phone' => '+1 555 444 5555',
                'status' => TenantJob::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('client.jobs.edit', $job))
            ->assertSessionHas('status', 'Job updated.');

        $job->refresh();

        $this->assertSame('Published Public Role', $job->title);
        $this->assertSame(TenantJob::STATUS_PUBLISHED, $job->status);
        $this->assertNotNull($job->published_at);
    }

    public function test_tenant_owner_cannot_edit_another_owners_job(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');
        $otherCompany = TenantCompany::query()->create([
            'tenant_id' => $otherTenant->id,
            'organization_name' => 'Other Group',
            'name' => 'Other Hiring',
            'slug' => 'other-hiring',
        ]);
        $job = TenantJob::query()->create([
            'tenant_id' => $otherTenant->id,
            'tenant_company_id' => $otherCompany->id,
            'company_name' => 'Other Hiring',
            'contact_name' => 'Other Contact',
            'contact_email' => 'other@example.com',
            'title' => 'Blocked Role',
            'slug' => 'blocked-role',
            'location' => 'Remote',
            'employment_type' => 'Full time',
            'description' => '<p>Not owned by this user.</p>',
            'status' => TenantJob::STATUS_DRAFT,
        ]);

        $this->actingAs($owner)
            ->get('/client/dashboard/jobs/'.$job->id.'/edit')
            ->assertNotFound();

        $this->actingAs($owner)
            ->patch('/client/dashboard/jobs/'.$job->id, [
                'tenant_id' => $otherTenant->id,
                'tenant_company_id' => $otherCompany->id,
                'title' => 'Should Not Update',
                'location' => 'Remote',
                'employment_type' => 'Full time',
                'description' => '<p>Blocked.</p>',
                'contact_first_name' => 'Other',
                'contact_last_name' => 'Contact',
                'contact_email' => 'other@example.com',
                'status' => TenantJob::STATUS_PUBLISHED,
            ])
            ->assertNotFound();

        $this->assertSame('Blocked Role', $job->fresh()->title);
    }

    public function test_tenant_owner_can_create_company_with_logo(): void
    {
        Storage::fake(PublicUploadStorage::diskName());

        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $this->actingAs($owner)
            ->get('/client/dashboard/companies/create')
            ->assertOk()
            ->assertSee('dash-form-layout', false)
            ->assertSee('Create company')
            ->assertSee('Organization name')
            ->assertSee('Company name (for job posts)')
            ->assertDontSee('Select environment')
            ->assertSee('Company logo')
            ->assertSee('Contact details')
            ->assertSee('First name')
            ->assertSee('Last name')
            ->assertSee('Email address')
            ->assertSee('Phone number')
            ->assertSee('Choose file')
            ->assertSee('No file selected')
            ->assertSee('Company description')
            ->assertSee('Upload a PNG, JPG, WebP or SVG logo.')
            ->assertSee('data-quill-field', false)
            ->assertSee('cdn.jsdelivr.net/npm/quill@2/dist/quill.js', false)
            ->assertDontSee('placeholder=', false);

        $this->actingAs($owner)
            ->post('/client/dashboard/companies', [
                'tenant_id' => $tenant->id,
                'organization_name' => 'Northwind Group',
                'name' => 'Northwind Hiring',
                'contact_first_name' => 'Maya',
                'contact_last_name' => 'Collins',
                'contact_email' => 'maya@example.com',
                'contact_phone' => '+31 20 123 4567',
                'description' => '<p>Hiring team for <strong>seasonal</strong> roles.</p><script>alert("xss")</script>',
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
        $this->assertSame('<p>Hiring team for <strong>seasonal</strong> roles.</p>', $company->description);
        $this->assertNotNull($company->logo_path);
        Storage::disk(PublicUploadStorage::diskName())->assertExists($company->logo_path);

        $this->actingAs($owner)
            ->get('/client/dashboard/companies')
            ->assertOk()
            ->assertSee('Northwind Hiring')
            ->assertSee('/client/dashboard/companies/'.$company->id.'/edit', false)
            ->assertSee('Northwind Group')
            ->assertSee('Maya Collins')
            ->assertSee('storage/tenants/'.$tenant->id.'/company-logos', false);
    }

    public function test_tenant_owner_can_edit_company_from_the_client_dashboard(): void
    {
        Storage::fake(PublicUploadStorage::diskName());

        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $company = TenantCompany::query()->create([
            'tenant_id' => $tenant->id,
            'organization_name' => 'Northwind Group',
            'name' => 'Northwind Hiring',
            'slug' => 'northwind-hiring',
            'contact_first_name' => 'Maya',
            'contact_last_name' => 'Collins',
            'contact_name' => 'Maya Collins',
            'contact_email' => 'maya@example.com',
            'contact_phone' => '+31 20 123 4567',
            'logo_path' => 'company-logos/original.svg',
            'description' => '<p>Original company description.</p>',
        ]);
        $job = TenantJob::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_company_id' => $company->id,
            'company_name' => 'Northwind Hiring',
            'company_logo_path' => 'company-logos/original.svg',
            'contact_name' => 'Maya Collins',
            'contact_email' => 'maya@example.com',
            'title' => 'Operations Lead',
            'slug' => 'operations-lead',
            'location' => 'Amsterdam',
            'employment_type' => 'Full time',
            'description' => '<p>Lead operations.</p>',
            'status' => TenantJob::STATUS_DRAFT,
        ]);

        $this->actingAs($owner)
            ->get('/client/dashboard/companies/'.$company->id.'/edit')
            ->assertOk()
            ->assertSee('Edit company')
            ->assertSee('Northwind Group')
            ->assertSee('Northwind Hiring')
            ->assertSee('value="Maya"', false)
            ->assertSee('value="Collins"', false)
            ->assertDontSee('placeholder=', false);

        $this->actingAs($owner)
            ->patch('/client/dashboard/companies/'.$company->id, [
                'tenant_id' => $tenant->id,
                'organization_name' => 'Northwind Collective',
                'name' => 'Northwind Talent',
                'contact_first_name' => 'Nina',
                'contact_last_name' => 'Owner',
                'contact_email' => 'nina@example.com',
                'contact_phone' => '+31 20 987 6543',
                'description' => '<p>Updated <strong>company</strong> profile.</p><script>alert("xss")</script>',
                'logo' => UploadedFile::fake()->create('northwind-updated.png', 32, 'image/png'),
            ])
            ->assertRedirect(route('client.companies.edit', $company))
            ->assertSessionHas('status', 'Company updated.');

        $company->refresh();
        $job->refresh();

        $this->assertSame('Northwind Collective', $company->organization_name);
        $this->assertSame('Northwind Talent', $company->name);
        $this->assertSame('northwind-talent', $company->slug);
        $this->assertSame('Nina', $company->contact_first_name);
        $this->assertSame('Owner', $company->contact_last_name);
        $this->assertSame('Nina Owner', $company->contact_name);
        $this->assertSame('nina@example.com', $company->contact_email);
        $this->assertSame('+31 20 987 6543', $company->contact_phone);
        $this->assertSame('<p>Updated <strong>company</strong> profile.</p>', $company->description);
        $this->assertNotNull($company->logo_path);
        $this->assertNotSame('company-logos/original.svg', $company->logo_path);
        Storage::disk(PublicUploadStorage::diskName())->assertExists($company->logo_path);
        $this->assertSame('Northwind Talent', $job->company_name);
        $this->assertSame($company->logo_path, $job->company_logo_path);
    }

    public function test_tenant_owner_cannot_create_company_for_unowned_environment(): void
    {
        Storage::fake(PublicUploadStorage::diskName());

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
        Storage::disk(PublicUploadStorage::diskName())->assertMissing('tenants/'.$otherTenant->id.'/company-logos/blocked-logo.png');
    }

    public function test_tenant_owner_cannot_edit_another_owners_company(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');
        $company = TenantCompany::query()->create([
            'tenant_id' => $otherTenant->id,
            'organization_name' => 'Other Group',
            'name' => 'Other Hiring',
            'slug' => 'other-hiring',
        ]);

        $this->actingAs($owner)
            ->get('/client/dashboard/companies/'.$company->id.'/edit')
            ->assertNotFound();

        $this->actingAs($owner)
            ->patch('/client/dashboard/companies/'.$company->id, [
                'tenant_id' => $otherTenant->id,
                'organization_name' => 'Blocked Group',
                'name' => 'Blocked Company',
            ])
            ->assertNotFound();

        $this->assertSame('Other Hiring', $company->fresh()->name);
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

    public function test_tenant_owner_can_manage_job_setting_options_for_owned_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $optionPages = [
            '/client/dashboard/jobs-settings/sector' => [
                'title' => 'Sectors',
                'setting' => 'custom_sectors',
                'name' => 'Healthcare',
                'status' => 'Sector added.',
            ],
            '/client/dashboard/jobs-settings/categorie' => [
                'title' => 'Categories',
                'setting' => 'custom_categories',
                'name' => 'Marketing',
                'status' => 'Category added.',
            ],
            '/client/dashboard/jobs-settings/organization-type' => [
                'title' => 'Organization types',
                'setting' => 'custom_organization_types',
                'name' => 'Non-profit',
                'status' => 'Organization type added.',
            ],
        ];

        foreach ($optionPages as $path => $optionPage) {
            $this->actingAs($owner)
                ->get($path)
                ->assertOk()
                ->assertSee($optionPage['title'])
                ->assertSee('dash-form-layout', false)
                ->assertSee('dash-form-layout__aside', false);

            $this->actingAs($owner)
                ->post($path, [
                    'tenant_id' => $tenant->id,
                    'name' => $optionPage['name'],
                ])
                ->assertRedirect($path)
                ->assertSessionHas('status', $optionPage['status']);

            $tenant->refresh();

            $this->assertSame([$optionPage['name']], $tenant->settings[$optionPage['setting']] ?? []);

            $this->actingAs($owner)
                ->get($path)
                ->assertOk()
                ->assertSee($optionPage['name']);
        }
    }

    public function test_tenant_owner_can_manage_packages_for_owned_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');

        $this->actingAs($owner)
            ->get('/client/dashboard/packages')
            ->assertOk()
            ->assertSee('My packages')
            ->assertSee('Add packages')
            ->assertSee('No packages yet')
            ->assertDontSee('Package name');

        $this->actingAs($owner)
            ->get('/client/dashboard/packages/create')
            ->assertOk()
            ->assertSee('Add packages')
            ->assertSee('Package name')
            ->assertSee('Price')
            ->assertSee('Currency')
            ->assertSee('Days online');

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

    public function test_tenant_owner_cannot_add_duplicate_or_unowned_job_setting_options(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs-settings/sector', [
                'tenant_id' => $tenant->id,
                'name' => 'Healthcare',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs-settings/sector', [
                'tenant_id' => $tenant->id,
                'name' => 'healthcare',
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($owner)
            ->post('/client/dashboard/jobs-settings/categorie', [
                'tenant_id' => $otherTenant->id,
                'name' => 'Marketing',
            ])
            ->assertSessionHasErrors('tenant_id');

        $tenant->refresh();
        $otherTenant->refresh();

        $this->assertSame(['Healthcare'], $tenant->settings['custom_sectors'] ?? []);
        $this->assertArrayNotHasKey('custom_categories', $otherTenant->settings ?? []);
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

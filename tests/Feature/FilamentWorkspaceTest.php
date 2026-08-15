<?php

namespace Tests\Feature;

use App\Filament\Workspace\Pages\Billing;
use App\Filament\Workspace\Resources\Domains\Pages\EditDomain;
use App\Filament\Workspace\Resources\Jobs\Pages\CreateJob;
use App\Models\BillingPlan;
use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_login_page_is_available(): void
    {
        $this->get('/workspace/login')->assertOk();
    }

    public function test_tenant_owner_can_access_workspace_pages(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
            'billing_status' => 'trial',
        ]);

        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $domain = $tenant->domains()->firstOrFail();
        $job = TenantJob::create([
            'tenant_id' => $tenant->id,
            'title' => 'Sustainable Recruiter',
            'slug' => 'sustainable-recruiter',
            'location' => 'Amsterdam',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $application = JobApplication::create([
            'tenant_id' => $tenant->id,
            'tenant_job_id' => $job->id,
            'name' => 'Nina Applicant',
            'email' => 'nina@example.com',
            'status' => JobApplication::STATUS_NEW,
        ]);

        foreach ([
            '/workspace',
            '/workspace/environments',
            '/workspace/jobs',
            '/workspace/domains',
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
            '/workspace/environments/create',
            '/workspace/environments/'.$tenant->id,
            '/workspace/environments/'.$tenant->id.'/edit',
            '/workspace/jobs/create',
            '/workspace/jobs/'.$job->id,
            '/workspace/jobs/'.$job->id.'/edit',
            '/workspace/domains/create',
            '/workspace/domains/'.$domain->id,
            '/workspace/domains/'.$domain->id.'/edit',
            '/workspace/applications/'.$application->id,
            '/workspace/applications/'.$application->id.'/edit',
        ] as $path) {
            $this->actingAs($owner)->get($path)->assertOk();
        }
    }

    public function test_tenant_owner_only_sees_their_own_environments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);

        $this->tenantFor($owner, 'Acme Careers', 'acme-careers');
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers');

        $this->actingAs($owner)
            ->get('/workspace/environments')
            ->assertOk()
            ->assertSee('Acme Careers')
            ->assertDontSee('Other Careers');

        $this->actingAs($owner)
            ->get('/workspace/environments/'.$otherTenant->id)
            ->assertNotFound();
    }

    public function test_tenant_owner_cannot_access_admin_panel(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);

        $this->actingAs($owner)
            ->get('/filament')
            ->assertForbidden();
    }

    public function test_tenant_owner_cannot_view_another_tenants_job(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers-job');

        $otherJob = TenantJob::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->actingAs($owner)
            ->get('/workspace/jobs/'.$otherJob->id)
            ->assertNotFound();
    }

    public function test_tenant_owner_cannot_view_another_tenants_domain(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers-domain');

        $otherDomain = Domain::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->actingAs($owner)
            ->get('/workspace/domains/'.$otherDomain->id)
            ->assertNotFound();
    }

    public function test_tenant_owner_cannot_view_another_tenants_application(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers-application');
        $otherJob = TenantJob::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherApplication = JobApplication::factory()->create([
            'tenant_id' => $otherTenant->id,
            'tenant_job_id' => $otherJob->id,
        ]);

        $this->actingAs($owner)
            ->get('/workspace/applications/'.$otherApplication->id)
            ->assertNotFound();
    }

    public function test_tenant_owner_cannot_create_a_job_for_another_tenant(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherOwner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $otherTenant = $this->tenantFor($otherOwner, 'Other Careers', 'other-careers-tamper');

        $this->actingAs($owner)->get('/workspace/jobs/create');

        Livewire::test(CreateJob::class)
            ->fillForm([
                'tenant_id' => $otherTenant->id,
                'title' => 'Tampered Job',
                'slug' => 'tampered-job',
                'status' => TenantJob::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['tenant_id']);

        $this->assertDatabaseMissing('tenant_jobs', ['slug' => 'tampered-job']);
    }

    public function test_dns_check_marks_an_unverified_domain_as_failed(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers-dns');

        $domain = Domain::factory()->create([
            'tenant_id' => $tenant->id,
            'domain' => 'unverified-'.Str::lower(Str::random(8)).'.example.com',
            'is_primary' => false,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
            'verified_at' => null,
            'verification_payload' => [
                'type' => 'CNAME',
                'value' => 'cname.jobboardsoftware.co',
                'txt_name' => '_jobboardsoftware.example.com',
                'txt_value' => 'nonexistent-token',
            ],
        ]);

        $this->actingAs($owner)->get('/workspace/domains/'.$domain->id.'/edit');

        Livewire::test(EditDomain::class, ['record' => $domain->getKey()])
            ->callAction('checkDns');

        $this->assertSame(Domain::STATUS_FAILED, $domain->fresh()->status);
    }

    public function test_ssl_can_be_activated_once_a_domain_is_verified(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $tenant = $this->tenantFor($owner, 'Acme Careers', 'acme-careers-ssl');

        $domain = Domain::factory()->create([
            'tenant_id' => $tenant->id,
            'is_primary' => false,
            'status' => Domain::STATUS_VERIFIED,
            'ssl_status' => Domain::SSL_PENDING,
        ]);

        $this->actingAs($owner)->get('/workspace/domains/'.$domain->id.'/edit');

        Livewire::test(EditDomain::class, ['record' => $domain->getKey()])
            ->callAction('activateSsl');

        $domain->refresh();

        $this->assertSame(Domain::STATUS_ACTIVE, $domain->status);
        $this->assertSame(Domain::SSL_ACTIVE, $domain->ssl_status);
        $this->assertNotNull($domain->ssl_issued_at);
    }

    public function test_tenant_owner_can_select_a_billing_plan(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_TENANT_OWNER]);
        $this->tenantFor($owner, 'Acme Careers', 'acme-careers-billing');

        $plan = BillingPlan::factory()->create([
            'key' => 'growth',
            'stripe_price_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($owner)->get('/workspace/billing');

        Livewire::test(Billing::class)
            ->callAction('selectPlan', arguments: ['plan' => $plan->key])
            ->assertHasNoActionErrors();

        $owner->refresh();

        $this->assertSame($plan->id, $owner->billing_plan_id);
        $this->assertSame('trial', $owner->billing_status);
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

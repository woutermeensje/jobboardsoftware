<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_credentials_for_the_wrong_portal(): void
    {
        User::factory()->create([
            'email' => 'admin-only@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'admin-only@example.com',
                'password' => 'password123',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_form_signs_the_user_out(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
        ]);

        $this->actingAs($owner)
            ->post('/logout')
            ->assertRedirect(route('login.choice'));

        $this->assertGuest();
    }

    public function test_domain_forms_add_check_and_activate_ssl(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();

        $tenant->domains()->create([
            'domain' => 'hire-labs.test',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        $this->actingAs($owner)
            ->post(route('tenant.environments.domains.store', $tenant), [
                'domain' => 'https://jobs.hire-labs.test/careers',
            ])
            ->assertRedirect(route('tenant.environments.index'));

        $domain = Domain::query()->where('domain', 'jobs.hire-labs.test')->firstOrFail();

        $this->assertFalse($domain->is_primary);
        $this->assertSame(Domain::STATUS_PENDING, $domain->status);

        $this->actingAs($owner)
            ->post(route('tenant.environments.domains.check', [$tenant, $domain]))
            ->assertSessionHas('status', 'DNS records were not found yet. Check the CNAME or TXT value and try again.');

        $domain->refresh();
        $this->assertSame(Domain::STATUS_FAILED, $domain->status);

        $domain->forceFill(['status' => Domain::STATUS_VERIFIED])->save();

        $this->actingAs($owner)
            ->post(route('tenant.environments.domains.ssl', [$tenant, $domain]))
            ->assertSessionHas('status', 'SSL status has been set to active. Connect the certificate provider here later.');

        $domain->refresh();
        $this->assertSame(Domain::STATUS_ACTIVE, $domain->status);
        $this->assertSame(Domain::SSL_ACTIVE, $domain->ssl_status);
        $this->assertNotNull($domain->ssl_issued_at);
    }

    public function test_job_forms_create_update_and_delete_jobs(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();

        $this->actingAs($owner)
            ->post(route('tenant.jobs.store', $tenant), [
                'title' => 'Backend Developer',
                'department' => 'Engineering',
                'location' => 'Amsterdam',
                'employment_type' => 'Fulltime',
                'salary_range' => 'EUR 5000 - 6500',
                'intro' => 'Build the platform.',
                'description' => 'Work on Laravel features.',
                'status' => TenantJob::STATUS_DRAFT,
            ])
            ->assertRedirect(route('tenant.jobs.index', $tenant));

        $job = TenantJob::query()->where('slug', 'backend-developer')->firstOrFail();

        $this->assertSame('complete', $owner->fresh()->onboarding_step);

        $this->actingAs($owner)
            ->put(route('tenant.jobs.update', [$tenant, $job]), [
                'title' => 'Senior Backend Developer',
                'slug' => 'senior-backend-developer',
                'department' => 'Engineering',
                'location' => 'Rotterdam',
                'employment_type' => 'Fulltime',
                'salary_range' => 'EUR 6000 - 7500',
                'intro' => 'Lead backend work.',
                'description' => 'Own the Laravel platform.',
                'status' => TenantJob::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('tenant.jobs.index', $tenant));

        $job->refresh();
        $this->assertSame('Senior Backend Developer', $job->title);
        $this->assertSame('senior-backend-developer', $job->slug);
        $this->assertSame(TenantJob::STATUS_PUBLISHED, $job->status);
        $this->assertNotNull($job->published_at);

        $this->actingAs($owner)
            ->delete(route('tenant.jobs.destroy', [$tenant, $job]))
            ->assertRedirect(route('tenant.jobs.index', $tenant));

        $this->assertDatabaseMissing('tenant_jobs', [
            'id' => $job->id,
        ]);
    }

    public function test_candidate_application_form_accepts_cv_upload(): void
    {
        config([
            'tenancy.bootstrappers' => collect(config('tenancy.bootstrappers'))
                ->reject(fn (string $bootstrapper): bool => $bootstrapper === FilesystemTenancyBootstrapper::class)
                ->values()
                ->all(),
        ]);

        Storage::fake('public');

        $tenant = Tenant::create([
            'id' => 'candidate-flow',
            'name' => 'Candidate Flow',
            'slug' => 'candidate-flow',
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['brand_name' => 'Candidate Flow'],
        ]);

        $tenant->domains()->create([
            'domain' => 'candidate-flow.test',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        $job = $tenant->jobs()->create([
            'title' => 'Support Specialist',
            'slug' => 'support-specialist',
            'department' => 'Support',
            'location' => 'Utrecht',
            'employment_type' => 'Parttime',
            'description' => 'Help customers succeed.',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->post('http://candidate-flow.test/jobs/support-specialist/apply', [
            'name' => 'Sam Applicant',
            'email' => 'sam@example.com',
            'phone' => '0612345678',
            'motivation' => 'This role fits me well.',
            'cv' => UploadedFile::fake()->create('sam-cv.pdf', 64, 'application/pdf'),
        ])->assertRedirect('http://candidate-flow.test/jobs/support-specialist');

        $application = JobApplication::query()
            ->where('tenant_job_id', $job->id)
            ->where('email', 'sam@example.com')
            ->firstOrFail();

        $this->assertSame(JobApplication::STATUS_NEW, $application->status);
        $this->assertNotNull($application->cv_path);
    }

    public function test_application_status_form_updates_the_application(): void
    {
        [$owner, $tenant] = $this->ownerAndTenant();

        $job = $tenant->jobs()->create([
            'title' => 'Account Manager',
            'slug' => 'account-manager',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $application = JobApplication::create([
            'tenant_id' => $tenant->id,
            'tenant_job_id' => $job->id,
            'name' => 'Alex Candidate',
            'email' => 'alex@example.com',
            'status' => JobApplication::STATUS_NEW,
        ]);

        $this->actingAs($owner)
            ->patch(route('tenant.applications.update', [$tenant, $application]), [
                'status' => JobApplication::STATUS_REVIEWED,
            ])
            ->assertSessionHas('status', 'Application status updated.');

        $this->assertSame(JobApplication::STATUS_REVIEWED, $application->fresh()->status);
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function ownerAndTenant(): array
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

        return [$owner, $tenant];
    }
}

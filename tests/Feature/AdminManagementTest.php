<?php

namespace Tests\Feature;

use App\Mail\AdminActionNotification;
use App\Models\BillingPlan;
use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_billing_and_application_actions_send_notifications_to_admin_email(): void
    {
        config(['admin.email' => 'admin@example.com']);
        Mail::fake();
        Notification::fake();

        $plan = BillingPlan::create([
            'key' => Tenant::PLAN_STARTER,
            'name' => 'Starter',
            'description' => 'Starter package',
            'monthly_price_cents' => 4900,
            'currency' => 'eur',
            'features' => ['1 job board'],
            'limits' => ['tenants' => 1],
            'is_active' => true,
        ]);

        $this->post('/sign-up', [
            'first_name' => 'Nina',
            'last_name' => 'Owner',
            'email' => 'nina@example.com',
            'phone_number' => '0612345678',
            'heard_about_us' => 'Google',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        $owner = User::where('email', 'nina@example.com')->firstOrFail();
        $owner->forceFill([
            'email_verified_at' => now(),
            'company_name' => 'Nina Careers',
            'billing_plan_id' => $plan->id,
            'onboarding_step' => 'billing',
        ])->save();

        $this->actingAs($owner)->get('/client/dashboard')->assertOk();
        $this->actingAs($owner)->get('/client/dashboard/billing')->assertOk();

        $this->actingAs($owner)
            ->get(route('billing.success'))
            ->assertRedirect(route('client.billing'));

        $tenant = Tenant::create([
            'id' => 'nina-careers',
            'owner_user_id' => $owner->id,
            'name' => 'Nina Careers',
            'slug' => 'nina-careers',
            'plan' => $plan->key,
            'status' => Tenant::STATUS_ACTIVE,
            'billing_status' => 'active',
            'onboarding_step' => 'jobs',
        ]);

        $tenant->domains()->create([
            'domain' => 'nina-careers.jobboardsoftware.co',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
        ]);

        $job = $tenant->jobs()->create([
            'title' => 'Senior Backend Developer',
            'slug' => 'senior-backend-developer',
            'department' => 'Engineering',
            'location' => 'Amsterdam',
            'employment_type' => 'Fulltime',
            'description' => 'Build the platform.',
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->post('http://nina-careers.jobboardsoftware.co/jobs/senior-backend-developer/apply', [
            'name' => 'Sam Candidate',
            'email' => 'sam@example.com',
            'motivation' => 'I like this role.',
        ])->assertRedirect('http://nina-careers.jobboardsoftware.co/jobs/senior-backend-developer');

        $application = JobApplication::where('email', 'sam@example.com')->firstOrFail();

        foreach ([
            'New user registered',
            'Trial started',
            'New application received',
        ] as $title) {
            $this->assertAdminMailWasSent($title);
        }

        $this->assertSame($job->id, $application->tenant_job_id);
    }

    public function test_admin_can_manage_platform_records(): void
    {
        [$admin, $owner, $plan, $tenant, $domain, $job, $application] = $this->platformRecords();

        foreach ([
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.tenants.index'),
            route('admin.domains.index'),
            route('admin.jobs.index'),
            route('admin.applications.index'),
        ] as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertStatus(200)
                ->assertSee('dashboard-topbar', false)
                ->assertSee('dashboard-sidebar', false)
                ->assertDontSee('dashboard-topbar__heading', false);
        }

        $this->actingAs($owner)->get(route('admin.users.index'))->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $owner), [
                'name' => 'Updated Owner',
                'company_name' => 'Updated Company',
                'role' => User::ROLE_TENANT_OWNER,
                'billing_plan_id' => $plan->id,
                'billing_status' => 'active',
                'onboarding_step' => 'complete',
            ])
            ->assertSessionHas('status', 'User updated.');

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'Updated Owner',
            'company_name' => 'Updated Company',
            'billing_status' => 'active',
            'onboarding_step' => 'complete',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.tenants.update', $tenant), [
                'name' => 'Updated Careers',
                'plan' => $plan->key,
                'status' => Tenant::STATUS_SUSPENDED,
                'billing_status' => 'past_due',
                'onboarding_step' => 'complete',
            ])
            ->assertSessionHas('status', 'Tenant updated.');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Updated Careers',
            'status' => Tenant::STATUS_SUSPENDED,
            'billing_status' => 'past_due',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.domains.update', $domain), [
                'status' => Domain::STATUS_ACTIVE,
                'ssl_status' => Domain::SSL_ACTIVE,
                'is_primary' => '1',
            ])
            ->assertSessionHas('status', 'Domain updated.');

        $domain->refresh();
        $this->assertTrue($domain->is_primary);
        $this->assertSame(Domain::STATUS_ACTIVE, $domain->status);
        $this->assertSame(Domain::SSL_ACTIVE, $domain->ssl_status);

        $this->actingAs($admin)
            ->patch(route('admin.jobs.update', $job), [
                'status' => TenantJob::STATUS_CLOSED,
            ])
            ->assertSessionHas('status', 'Job updated.');

        $this->assertSame(TenantJob::STATUS_CLOSED, $job->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.applications.update', $application), [
                'status' => JobApplication::STATUS_HIRED,
            ])
            ->assertSessionHas('status', 'Application updated.');

        $this->assertSame(JobApplication::STATUS_HIRED, $application->fresh()->status);
    }

    private function assertAdminMailWasSent(string $title): void
    {
        Mail::assertSent(
            AdminActionNotification::class,
            fn (AdminActionNotification $mail): bool => $mail->hasTo('admin@example.com')
                && $mail->title === $title,
        );
    }

    /**
     * @return array{0: User, 1: User, 2: BillingPlan, 3: Tenant, 4: Domain, 5: TenantJob, 6: JobApplication}
     */
    private function platformRecords(): array
    {
        $plan = BillingPlan::create([
            'key' => Tenant::PLAN_GROWTH,
            'name' => 'Growth',
            'description' => 'Growth package',
            'monthly_price_cents' => 14900,
            'currency' => 'eur',
            'features' => ['3 job boards'],
            'limits' => ['tenants' => 3],
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $owner = User::factory()->create([
            'role' => User::ROLE_TENANT_OWNER,
            'billing_plan_id' => $plan->id,
        ]);

        $tenant = Tenant::create([
            'id' => 'admin-managed',
            'owner_user_id' => $owner->id,
            'name' => 'Admin Managed',
            'slug' => 'admin-managed',
            'plan' => $plan->key,
            'status' => Tenant::STATUS_ACTIVE,
            'billing_status' => 'trial',
            'onboarding_step' => 'jobs',
        ]);

        $domain = $tenant->domains()->create([
            'domain' => 'admin-managed.test',
            'is_primary' => false,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
        ]);

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

        return [$admin, $owner, $plan, $tenant, $domain, $job, $application];
    }
}

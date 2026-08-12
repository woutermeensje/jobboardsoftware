<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.dashboard', [
            'user' => $request->user(),
            'stats' => [
                'users' => User::count(),
                'tenants' => Tenant::count(),
                'domains' => Domain::count(),
                'jobs' => TenantJob::count(),
                'applications' => JobApplication::count(),
            ],
            'tenants' => Tenant::with(['owner', 'domains'])->latest()->take(8)->get(),
            'domains' => Domain::with('tenant')->latest()->take(8)->get(),
            'users' => User::with('billingPlan')->latest()->take(8)->get(),
            'applications' => JobApplication::with(['tenant', 'job'])->latest()->take(8)->get(),
        ]);
    }

    public function users(Request $request): View
    {
        return view('admin.users', [
            'user' => $request->user(),
            'users' => User::with('billingPlan')->withCount('ownedTenants')->latest()->get(),
            'plans' => BillingPlan::query()->orderBy('monthly_price_cents')->get(),
        ]);
    }

    public function updateUser(Request $request, User $managedUser): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in([
                User::ROLE_TENANT_OWNER,
                User::ROLE_ADMIN,
                User::ROLE_WERKGEVER,
                User::ROLE_WERKZOEKENDE,
            ])],
            'billing_plan_id' => ['nullable', 'exists:billing_plans,id'],
            'billing_status' => ['required', Rule::in(['trial', 'active', 'past_due', 'canceled'])],
            'onboarding_step' => ['required', Rule::in(['plan', 'environment', 'domain', 'jobs', 'billing', 'complete'])],
        ]);

        abort_if(
            (int) $managedUser->id === (int) $request->user()->id && $validated['role'] !== User::ROLE_ADMIN,
            422,
            'You cannot remove your own admin role.',
        );

        $managedUser->forceFill([
            ...$validated,
            'billing_plan_id' => $validated['billing_plan_id'] ?: null,
        ])->save();

        return back()->with('status', 'User updated.');
    }

    public function tenants(Request $request): View
    {
        return view('admin.tenants', [
            'user' => $request->user(),
            'tenants' => Tenant::with(['owner', 'domains'])->withCount(['jobs', 'applications'])->latest()->get(),
            'plans' => BillingPlan::query()->orderBy('monthly_price_cents')->get(),
        ]);
    }

    public function updateTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'exists:billing_plans,key'],
            'status' => ['required', Rule::in([
                Tenant::STATUS_TRIAL,
                Tenant::STATUS_ACTIVE,
                Tenant::STATUS_SUSPENDED,
            ])],
            'billing_status' => ['required', Rule::in(['trial', 'active', 'past_due', 'canceled'])],
            'onboarding_step' => ['required', Rule::in(['domain', 'jobs', 'complete'])],
        ]);

        $tenant->forceFill($validated)->save();

        return back()->with('status', 'Tenant updated.');
    }

    public function domains(Request $request): View
    {
        return view('admin.domains', [
            'user' => $request->user(),
            'domains' => Domain::with('tenant.owner')->latest()->get(),
        ]);
    }

    public function updateDomain(Request $request, Domain $domain): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Domain::STATUS_PENDING,
                Domain::STATUS_VERIFIED,
                Domain::STATUS_ACTIVE,
                Domain::STATUS_FAILED,
            ])],
            'ssl_status' => ['required', Rule::in([
                Domain::SSL_PENDING,
                Domain::SSL_ACTIVE,
                Domain::SSL_FAILED,
            ])],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $isPrimary = (bool) ($validated['is_primary'] ?? false);

        if ($isPrimary) {
            Domain::query()
                ->where('tenant_id', $domain->tenant_id)
                ->whereKeyNot($domain->getKey())
                ->update(['is_primary' => false]);
        }

        $domain->forceFill([
            'status' => $validated['status'],
            'ssl_status' => $validated['ssl_status'],
            'is_primary' => $isPrimary,
            'verified_at' => in_array($validated['status'], [Domain::STATUS_VERIFIED, Domain::STATUS_ACTIVE], true)
                ? ($domain->verified_at ?? now())
                : null,
            'ssl_issued_at' => $validated['ssl_status'] === Domain::SSL_ACTIVE
                ? ($domain->ssl_issued_at ?? now())
                : null,
        ])->save();

        return back()->with('status', 'Domain updated.');
    }

    public function jobs(Request $request): View
    {
        return view('admin.jobs', [
            'user' => $request->user(),
            'jobs' => TenantJob::with('tenant.owner')->withCount('applications')->latest()->get(),
        ]);
    }

    public function updateJob(Request $request, TenantJob $job): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                TenantJob::STATUS_DRAFT,
                TenantJob::STATUS_PUBLISHED,
                TenantJob::STATUS_CLOSED,
            ])],
        ]);

        $job->forceFill([
            'status' => $validated['status'],
            'published_at' => $validated['status'] === TenantJob::STATUS_PUBLISHED
                ? ($job->published_at ?? now())
                : $job->published_at,
        ])->save();

        return back()->with('status', 'Job updated.');
    }

    public function applications(Request $request): View
    {
        return view('admin.applications', [
            'user' => $request->user(),
            'applications' => JobApplication::with(['tenant.owner', 'job'])->latest()->get(),
        ]);
    }

    public function updateApplication(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                JobApplication::STATUS_NEW,
                JobApplication::STATUS_REVIEWED,
                JobApplication::STATUS_REJECTED,
                JobApplication::STATUS_HIRED,
            ])],
        ]);

        $application->update($validated);

        return back()->with('status', 'Application updated.');
    }
}

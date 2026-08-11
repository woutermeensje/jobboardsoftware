<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantJobController extends Controller
{
    public function index(Request $request, Tenant $tenant): View
    {
        $this->authorizeTenant($request, $tenant);

        return view('dashboard.jobs.index', [
            'tenant' => $tenant,
            'jobs' => $tenant->jobs()->withCount('applications')->latest()->get(),
        ]);
    }

    public function create(Request $request, Tenant $tenant): View
    {
        $this->authorizeTenant($request, $tenant);

        return view('dashboard.jobs.form', [
            'tenant' => $tenant,
            'job' => new TenantJob(['status' => TenantJob::STATUS_DRAFT]),
            'action' => route('tenant.jobs.store', $tenant),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenant($request, $tenant);

        $validated = $this->validatedJob($request, $tenant);
        $status = $validated['status'];

        $tenant->jobs()->create([
            ...$validated,
            'slug' => Str::slug(($validated['slug'] ?? '') ?: $validated['title']),
            'published_at' => $status === TenantJob::STATUS_PUBLISHED ? now() : null,
        ]);

        $this->refreshOnboarding($request);

        return redirect()
            ->route('tenant.jobs.index', $tenant)
            ->with('status', 'Job created.');
    }

    public function edit(Request $request, Tenant $tenant, TenantJob $job): View
    {
        $this->authorizeTenantJob($request, $tenant, $job);

        return view('dashboard.jobs.form', [
            'tenant' => $tenant,
            'job' => $job,
            'action' => route('tenant.jobs.update', [$tenant, $job]),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Tenant $tenant, TenantJob $job): RedirectResponse
    {
        $this->authorizeTenantJob($request, $tenant, $job);

        $validated = $this->validatedJob($request, $tenant, $job);
        $wasPublished = $job->isPublished();
        $status = $validated['status'];

        $job->update([
            ...$validated,
            'slug' => Str::slug(($validated['slug'] ?? '') ?: $validated['title']),
            'published_at' => $status === TenantJob::STATUS_PUBLISHED && ! $wasPublished ? now() : $job->published_at,
        ]);

        return redirect()
            ->route('tenant.jobs.index', $tenant)
            ->with('status', 'Job updated.');
    }

    public function destroy(Request $request, Tenant $tenant, TenantJob $job): RedirectResponse
    {
        $this->authorizeTenantJob($request, $tenant, $job);

        $job->delete();

        return redirect()
            ->route('tenant.jobs.index', $tenant)
            ->with('status', 'Job deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedJob(Request $request, Tenant $tenant, ?TenantJob $job = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'alpha_dash',
                'max:255',
                Rule::unique('tenant_jobs', 'slug')
                    ->where('tenant_id', $tenant->id)
                    ->ignore($job?->id),
            ],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:255'],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in([TenantJob::STATUS_DRAFT, TenantJob::STATUS_PUBLISHED, TenantJob::STATUS_CLOSED])],
            'closes_at' => ['nullable', 'date'],
        ]);
    }

    private function authorizeTenant(Request $request, Tenant $tenant): void
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);
    }

    private function authorizeTenantJob(Request $request, Tenant $tenant, TenantJob $job): void
    {
        $this->authorizeTenant($request, $tenant);
        abort_unless($job->tenant_id === $tenant->id, 404);
    }

    private function refreshOnboarding(Request $request): void
    {
        $request->user()->forceFill([
            'onboarding_step' => 'complete',
            'onboarding_completed_at' => now(),
        ])->save();
    }
}

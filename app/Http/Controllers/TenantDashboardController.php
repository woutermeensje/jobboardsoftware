<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function account(Request $request): RedirectResponse
    {
        $user = $this->tenantUser($request);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return redirect()->route(
            $user->role === User::ROLE_EMPLOYER
                ? 'tenant.employer.dashboard'
                : 'tenant.jobseeker.dashboard',
        );
    }

    public function jobseeker(Request $request): View|RedirectResponse
    {
        $user = $this->tenantUser($request, User::ROLE_JOBSEEKER);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $applications = JobApplication::query()
            ->with('job')
            ->where('tenant_id', tenant('id'))
            ->where('email', $user->email)
            ->latest()
            ->take(6)
            ->get();

        $recommendedJobs = TenantJob::query()
            ->where('tenant_id', tenant('id'))
            ->where('status', TenantJob::STATUS_PUBLISHED)
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('tenant.dashboards.jobseeker', [
            ...$this->dashboardFrameData(),
            'user' => $user,
            'applications' => $applications,
            'recommendedJobs' => $recommendedJobs,
            'stats' => [
                ['label' => 'Open jobs', 'value' => TenantJob::query()->where('tenant_id', tenant('id'))->where('status', TenantJob::STATUS_PUBLISHED)->count()],
                ['label' => 'My applications', 'value' => $applications->count()],
                ['label' => 'In review', 'value' => $applications->where('status', JobApplication::STATUS_REVIEWED)->count()],
                ['label' => 'Saved jobs', 'value' => 0],
            ],
        ]);
    }

    public function employer(Request $request): View|RedirectResponse
    {
        $user = $this->tenantUser($request, User::ROLE_EMPLOYER);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $tenantId = tenant('id');
        $employerJobs = fn () => TenantJob::query()
            ->where('tenant_id', $tenantId)
            ->where('submitted_by_user_id', $user->id);

        $jobIds = TenantJob::query()
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('submitted_by_user_id', $user->id);
        $employerApplications = fn () => JobApplication::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('tenant_job_id', clone $jobIds);

        $jobs = $employerJobs()
            ->withCount('applications')
            ->latest()
            ->take(8)
            ->get();

        $applications = $employerApplications()
            ->with('job')
            ->latest()
            ->take(8)
            ->get();

        return view('tenant.dashboards.employer', [
            ...$this->dashboardFrameData(),
            'user' => $user,
            'jobs' => $jobs,
            'applications' => $applications,
            'stats' => [
                ['label' => 'Published jobs', 'value' => $employerJobs()->where('status', TenantJob::STATUS_PUBLISHED)->count()],
                ['label' => 'Draft jobs', 'value' => $employerJobs()->where('status', TenantJob::STATUS_DRAFT)->count()],
                ['label' => 'New applications', 'value' => $employerApplications()->where('status', JobApplication::STATUS_NEW)->count()],
                ['label' => 'Total applications', 'value' => $employerApplications()->count()],
            ],
        ]);
    }

    private function tenantUser(Request $request, ?string $role = null): User|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('tenant.login.choice');
        }

        abort_unless($user->tenant_id === tenant('id') && $user->isTenantScopedAccount(), 403);

        if ($role !== null) {
            abort_unless($user->role === $role, 403);
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardFrameData(): array
    {
        $tenant = tenant();
        $settings = $tenant?->settings ?? [];
        $brandName = $settings['brand_name'] ?? $tenant?->name ?? 'Jobboard';

        return [
            'tenant' => $tenant,
            'brandName' => $brandName,
            'dashboardBrandName' => $brandName,
            'dashboardBrandMark' => mb_strtoupper(mb_substr((string) $brandName, 0, 2)),
            'dashboardBrandUrl' => route('tenant.home'),
            'dashboardBrandAriaLabel' => $brandName.' home',
            'dashboardLogoutUrl' => route('tenant.logout'),
        ];
    }
}

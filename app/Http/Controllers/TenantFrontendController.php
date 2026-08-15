<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\TenantJob;
use App\Support\AdminActionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantFrontendController extends Controller
{
    public function home(Request $request): View
    {
        $tenant = tenant();
        $jobs = $this->jobsQuery($request)->get();
        $filterOptions = $this->filterOptions($tenant->id);

        return view('tenant.jobboard', [
            'tenant' => $tenant,
            'jobs' => $jobs,
            'departments' => $filterOptions['departments'],
            'locations' => $filterOptions['locations'],
            'employmentTypes' => $filterOptions['employmentTypes'],
            'departmentCounts' => $filterOptions['departmentCounts'],
            'employmentTypeCounts' => $filterOptions['employmentTypeCounts'],
            'totalJobs' => TenantJob::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', TenantJob::STATUS_PUBLISHED)
                ->count(),
            'focus' => null,
        ]);
    }

    public function jobs(Request $request): View
    {
        return $this->home($request);
    }

    public function showJob(TenantJob $job): View
    {
        abort_unless($job->tenant_id === tenant('id') && $job->isPublished(), 404);

        return view('tenant.job-show', [
            'tenant' => tenant(),
            'job' => $job,
        ]);
    }

    public function apply(Request $request, TenantJob $job): RedirectResponse
    {
        abort_unless($job->tenant_id === tenant('id') && $job->isPublished(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'motivation' => ['nullable', 'string', 'max:3000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $cvPath = null;

        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('applications/'.tenant('id'), 'public');
        }

        $application = JobApplication::create([
            'tenant_id' => tenant('id'),
            'tenant_job_id' => $job->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'motivation' => $validated['motivation'] ?? null,
            'cv_path' => $cvPath,
            'status' => JobApplication::STATUS_NEW,
        ]);

        app(AdminActionNotifier::class)->notify('Nieuwe sollicitatie ontvangen', [
            'tenant_id' => tenant('id'),
            'tenant_naam' => tenant('name'),
            'vacature' => $job->title,
            'sollicitant' => $application->name,
            'email' => $application->email,
            'telefoon' => $application->phone,
            'cv_geupload' => (bool) $application->cv_path,
        ]);

        return redirect()
            ->route('tenant.jobs.show', $job)
            ->with('status', 'Your application has been received.');
    }

    public function contact(): View
    {
        $tenant = tenant();
        $filterOptions = $this->filterOptions($tenant->id);

        return view('tenant.jobboard', [
            'tenant' => $tenant,
            'jobs' => $this->jobsQuery(request())->get(),
            'departments' => $filterOptions['departments'],
            'locations' => $filterOptions['locations'],
            'employmentTypes' => $filterOptions['employmentTypes'],
            'departmentCounts' => $filterOptions['departmentCounts'],
            'employmentTypeCounts' => $filterOptions['employmentTypeCounts'],
            'totalJobs' => TenantJob::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', TenantJob::STATUS_PUBLISHED)
                ->count(),
            'focus' => 'contact',
        ]);
    }

    private function jobsQuery(Request $request)
    {
        return TenantJob::query()
            ->where('tenant_id', tenant('id'))
            ->where('status', TenantJob::STATUS_PUBLISHED)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%')
                        ->orWhere('employment_type', 'like', '%'.$search.'%')
                        ->orWhere('intro', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('department'), fn ($query) => $query->whereIn('department', $this->selectedFilterValues($request, 'department')))
            ->when($request->filled('location'), fn ($query) => $query->where('location', 'like', '%'.$request->string('location')->toString().'%'))
            ->when($request->filled('employment_type'), fn ($query) => $query->whereIn('employment_type', $this->selectedFilterValues($request, 'employment_type')))
            ->latest('published_at');
    }

    /**
     * @return array{departments: \Illuminate\Support\Collection<int, string>, locations: \Illuminate\Support\Collection<int, string>, employmentTypes: \Illuminate\Support\Collection<int, string>, departmentCounts: array<string, int>, employmentTypeCounts: array<string, int>}
     */
    private function filterOptions(string $tenantId): array
    {
        $baseQuery = TenantJob::query()
            ->where('tenant_id', $tenantId)
            ->where('status', TenantJob::STATUS_PUBLISHED);

        return [
            'departments' => (clone $baseQuery)
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
            'locations' => (clone $baseQuery)
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->distinct()
                ->orderBy('location')
                ->pluck('location'),
            'employmentTypes' => (clone $baseQuery)
                ->whereNotNull('employment_type')
                ->where('employment_type', '!=', '')
                ->distinct()
                ->orderBy('employment_type')
                ->pluck('employment_type'),
            'departmentCounts' => (clone $baseQuery)
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->selectRaw('department, count(*) as aggregate')
                ->groupBy('department')
                ->pluck('aggregate', 'department')
                ->map(fn (mixed $count): int => (int) $count)
                ->all(),
            'employmentTypeCounts' => (clone $baseQuery)
                ->whereNotNull('employment_type')
                ->where('employment_type', '!=', '')
                ->selectRaw('employment_type, count(*) as aggregate')
                ->groupBy('employment_type')
                ->pluck('aggregate', 'employment_type')
                ->map(fn (mixed $count): int => (int) $count)
                ->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function selectedFilterValues(Request $request, string $key): array
    {
        return collect((array) $request->input($key, []))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }
}

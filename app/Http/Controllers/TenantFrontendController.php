<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\TenantJob;
use App\Models\User;
use App\Support\AdminActionNotifier;
use App\Support\JobTypeOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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

    public function showPostJob(): View
    {
        $tenant = tenant();
        $filterOptions = $this->filterOptions($tenant->id);

        return view('tenant.post-job', [
            'tenant' => $tenant,
            'brandName' => $this->tenantBrandName(),
            'categories' => $filterOptions['departments'],
            'jobTypes' => JobTypeOptions::allForTenant($tenant),
        ]);
    }

    public function storePostJob(Request $request): RedirectResponse
    {
        $tenant = tenant();
        $createAccount = $request->boolean('create_account');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'category' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'max:80', Rule::in(JobTypeOptions::allForTenant($tenant))],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:10000'],
            'create_account' => ['nullable', 'boolean'],
            'password' => [Rule::requiredIf($createAccount), 'nullable', 'confirmed', Password::min(8)],
        ]);

        if ($createAccount && User::query()->where('tenant_id', $tenant->id)->where('email', $validated['contact_email'])->exists()) {
            return back()
                ->withErrors(['contact_email' => 'An employer account already exists for this email address on this job board.'])
                ->withInput();
        }

        $submittedByUserId = $this->tenantEmployerUserId($request);

        if ($createAccount) {
            [$firstName, $lastName] = $this->splitName($validated['contact_name']);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['contact_name'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['contact_email'],
                'phone_number' => $validated['contact_phone'] ?? null,
                'company_name' => $validated['company_name'],
                'heard_about_us' => 'Public job posting',
                'password' => $validated['password'],
                'role' => User::ROLE_EMPLOYER,
            ]);

            $submittedByUserId = $user->id;
            Auth::login($user);
            $request->session()->regenerate();
        }

        $job = TenantJob::create([
            'tenant_id' => $tenant->id,
            'company_name' => $validated['company_name'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'submitted_by_user_id' => $submittedByUserId,
            'title' => $validated['title'],
            'slug' => $this->uniqueJobSlug($validated['title']),
            'department' => $validated['category'],
            'location' => $validated['location'],
            'employment_type' => $validated['employment_type'],
            'salary_range' => $validated['salary_range'] ?? null,
            'intro' => $validated['intro'] ?? null,
            'description' => $validated['description'],
            'status' => TenantJob::STATUS_DRAFT,
        ]);

        app(AdminActionNotifier::class)->notify('Public job submitted', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'job' => $job->title,
            'company' => $job->company_name,
            'contact_name' => $job->contact_name,
            'contact_email' => $job->contact_email,
            'account_created' => $createAccount,
        ]);

        if ($createAccount) {
            return redirect()
                ->route('tenant.employer.dashboard')
                ->with('status', 'Your job has been submitted as a draft.');
        }

        return redirect()
            ->route('tenant.post-job')
            ->with('status', 'Your job has been submitted as a draft.');
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

        app(AdminActionNotifier::class)->notify('New application received', [
            'tenant_id' => tenant('id'),
            'tenant_name' => tenant('name'),
            'job' => $job->title,
            'applicant' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'cv_uploaded' => (bool) $application->cv_path,
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

    private function tenantBrandName(): string
    {
        $tenant = tenant();
        $settings = $tenant?->settings ?? [];

        return $settings['brand_name'] ?? $tenant?->name ?? 'Jobboard';
    }

    private function tenantEmployerUserId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user || $user->tenant_id !== tenant('id') || $user->role !== User::ROLE_EMPLOYER) {
            return null;
        }

        return $user->id;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function splitName(string $name): array
    {
        $parts = Str::of($name)->squish()->explode(' ');
        $firstName = (string) $parts->shift();
        $lastName = $parts->isEmpty() ? null : $parts->implode(' ');

        return [$firstName, $lastName];
    }

    private function uniqueJobSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'job';
        $slug = $baseSlug;
        $counter = 2;

        while (TenantJob::query()->where('tenant_id', tenant('id'))->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

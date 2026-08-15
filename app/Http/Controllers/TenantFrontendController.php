<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\TenantCompany;
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
            'companies' => TenantCompany::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('name')
                ->get(['id', 'name', 'logo_path']),
            'jobTypes' => JobTypeOptions::allForTenant($tenant),
        ]);
    }

    public function storePostJob(Request $request): RedirectResponse
    {
        $tenant = tenant();
        $createAccount = $request->boolean('create_account');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'tenant_company_id' => [
                'nullable',
                Rule::exists('tenant_companies', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'company_name' => [Rule::requiredIf(! $request->filled('tenant_company_id')), 'nullable', 'string', 'max:255'],
            'company_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'category' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'max:80', Rule::in(JobTypeOptions::allForTenant($tenant))],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:10000'],
            'create_account' => ['nullable', 'boolean'],
            'password' => [Rule::requiredIf($createAccount), 'nullable', 'confirmed', Password::min(8)],
        ]);

        $intro = $this->sanitizeRichText($validated['intro'] ?? null);
        $description = $this->sanitizeRichText($validated['description']);
        $company = null;

        if (! empty($validated['tenant_company_id'])) {
            $company = TenantCompany::query()
                ->where('tenant_id', $tenant->id)
                ->find($validated['tenant_company_id']);
        }

        $companyName = $company?->name ?? $validated['company_name'] ?? null;

        if (! $companyName) {
            return back()
                ->withErrors(['company_name' => 'Enter a company name or select an existing company.'])
                ->withInput();
        }

        $companyLogoPath = $request->hasFile('company_logo')
            ? $request->file('company_logo')->store('company-logos', 'public')
            : $company?->logo_path;

        if ($description === null) {
            return back()
                ->withErrors(['description' => 'Enter a job description.'])
                ->withInput();
        }

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
                'company_name' => $companyName,
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
            'tenant_company_id' => $company?->id,
            'company_name' => $companyName,
            'company_logo_path' => $companyLogoPath,
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
            'intro' => $intro,
            'description' => $description,
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

    private function sanitizeRichText(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || in_array($value, ['<p><br></p>', '<p></p>'], true)) {
            return null;
        }

        if (! class_exists(\DOMDocument::class)) {
            $fallback = trim(strip_tags($value));

            return $fallback === '' ? null : e($fallback);
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="rich-text-root">'.$value.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rich-text-root');

        if (! $root) {
            return null;
        }

        $this->cleanRichTextNode($root);

        $html = '';

        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        $html = trim($html);

        return $html === '' ? null : $html;
    }

    private function cleanRichTextNode(\DOMNode $node): void
    {
        $allowedTags = [
            'a',
            'blockquote',
            'br',
            'code',
            'em',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'i',
            'li',
            'ol',
            'p',
            'pre',
            's',
            'strong',
            'u',
            'ul',
        ];

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }

            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);

                continue;
            }

            $tagName = mb_strtolower($child->tagName);

            if (in_array($tagName, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                $node->removeChild($child);

                continue;
            }

            $this->cleanRichTextNode($child);

            if (! in_array($tagName, $allowedTags, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }

                $node->removeChild($child);

                continue;
            }

            $this->cleanRichTextAttributes($child);
        }
    }

    private function cleanRichTextAttributes(\DOMElement $element): void
    {
        $href = $element->getAttribute('href');
        $classes = collect(explode(' ', $element->getAttribute('class')))
            ->filter(fn (string $class): bool => (bool) preg_match('/^ql-(align-(center|right|justify)|indent-[1-8])$/', $class))
            ->values()
            ->all();

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->name);
        }

        if ($classes !== []) {
            $element->setAttribute('class', implode(' ', $classes));
        }

        if (mb_strtolower($element->tagName) !== 'a' || $href === '') {
            return;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);
        $isRelative = $scheme === null && ! str_starts_with($href, '//');
        $isAllowedScheme = is_string($scheme) && in_array(mb_strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);

        if (! $isRelative && ! $isAllowedScheme) {
            return;
        }

        $element->setAttribute('href', $href);
        $element->setAttribute('rel', 'noopener noreferrer');
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

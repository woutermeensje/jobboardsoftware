<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\TenantCompany;
use App\Models\TenantJob;
use App\Models\TenantPackage;
use App\Models\User;
use App\Support\AdminActionNotifier;
use App\Support\CountryOptions;
use App\Support\JobTypeOptions;
use App\Support\PublicUploadStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
            'sectors' => $filterOptions['sectors'],
            'organizationTypes' => $filterOptions['organizationTypes'],
            'departmentCounts' => $filterOptions['departmentCounts'],
            'employmentTypeCounts' => $filterOptions['employmentTypeCounts'],
            'sectorCounts' => $filterOptions['sectorCounts'],
            'organizationTypeCounts' => $filterOptions['organizationTypeCounts'],
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

    public function showPostJob(Request $request): View
    {
        $tenant = tenant();
        $packages = $this->postJobPackages($tenant->id);
        $selectedPackageId = (string) $request->query('package', '');

        if ($selectedPackageId === '' || ! ctype_digit($selectedPackageId) || ! $packages->contains('id', (int) $selectedPackageId)) {
            $selectedPackageId = '';
        }

        return view('tenant.post-job', [
            'tenant' => $tenant,
            'brandName' => $this->tenantBrandName(),
            'jobTypes' => JobTypeOptions::allForTenant($tenant),
            'countries' => CountryOptions::all(),
            'packages' => $packages,
            'selectedPackageId' => $selectedPackageId,
        ]);
    }

    public function pricing(): View
    {
        $tenant = tenant();

        return view('tenant.pricing', [
            'tenant' => $tenant,
            'brandName' => $this->tenantBrandName(),
            'packages' => $this->pricingPackages($tenant->id),
        ]);
    }

    public function storePostJob(Request $request): RedirectResponse
    {
        $tenant = tenant();
        $createAccount = $request->boolean('create_account');
        $companyTableReady = Schema::hasTable('tenant_companies');
        $packageSelectionReady = $this->packageSelectionReady();
        $packagesAvailable = $packageSelectionReady
            && TenantPackage::query()->where('tenant_id', $tenant->id)->exists();
        $tenantCompanyIdRules = ['nullable'];
        $tenantPackageIdRules = ['nullable'];

        if ($companyTableReady) {
            $tenantCompanyIdRules[] = Rule::exists('tenant_companies', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id));
        }

        if ($packageSelectionReady) {
            $tenantPackageIdRules = $packagesAvailable ? ['required'] : ['nullable'];
            $tenantPackageIdRules[] = 'integer';
            $tenantPackageIdRules[] = Rule::exists('tenant_packages', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id));
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'tenant_package_id' => $tenantPackageIdRules,
            'tenant_company_id' => $tenantCompanyIdRules,
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_last_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', Rule::in(CountryOptions::codes())],
            'employment_type' => ['required', 'array', 'min:1'],
            'employment_type.*' => ['required', 'string', 'max:80', Rule::in(JobTypeOptions::allForTenant($tenant))],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:10000'],
            'create_account' => ['nullable', 'boolean'],
            'password' => [Rule::requiredIf($createAccount), 'nullable', 'confirmed', Password::min(8)],
        ]);

        $intro = $this->sanitizeRichText($validated['intro'] ?? null);
        $description = $this->sanitizeRichText($validated['description']);
        $contactName = trim($validated['contact_first_name'].' '.$validated['contact_last_name']);
        $employmentType = collect($validated['employment_type'])
            ->map(fn (string $type): string => JobTypeOptions::normalizeName($type))
            ->filter()
            ->unique(fn (string $type): string => mb_strtolower($type))
            ->values()
            ->implode(', ');
        $company = null;

        if ($companyTableReady && ! empty($validated['tenant_company_id'])) {
            $company = TenantCompany::query()
                ->where('tenant_id', $tenant->id)
                ->find($validated['tenant_company_id']);
        }

        $companyName = $company?->name ?? $validated['company_name'] ?? $this->tenantBrandName();

        if (! $companyName || mb_strlen($employmentType) > 255) {
            return back()
                ->withErrors($companyName ? ['employment_type' => 'Select fewer job types.'] : ['company_name' => 'Enter a company name or select an existing company.'])
                ->withInput();
        }

        $tenantJobsHasCompanyLogoPath = Schema::hasColumn('tenant_jobs', 'company_logo_path');
        $tenantJobsHasTenantCompanyId = Schema::hasColumn('tenant_jobs', 'tenant_company_id');
        $companyLogoPath = $tenantJobsHasCompanyLogoPath && $request->hasFile('company_logo')
            ? PublicUploadStorage::store($request->file('company_logo'), 'company-logos', $tenant->id)
            : ($tenantJobsHasCompanyLogoPath ? $company?->logo_path : null);

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
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $contactName,
                'first_name' => $validated['contact_first_name'],
                'last_name' => $validated['contact_last_name'],
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

        $jobAttributes = [
            'tenant_id' => $tenant->id,
            'company_name' => $companyName,
            'contact_name' => $contactName,
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'submitted_by_user_id' => $submittedByUserId,
            'title' => $validated['title'],
            'slug' => $this->uniqueJobSlug($validated['title']),
            'department' => $validated['category'] ?? null,
            'location' => $validated['location'] ?? null,
            'country' => filled($validated['country'] ?? null) ? CountryOptions::normalizeCode($validated['country']) : null,
            'employment_type' => $employmentType,
            'salary_range' => $validated['salary_range'] ?? null,
            'intro' => $intro,
            'description' => $description,
            'status' => TenantJob::STATUS_DRAFT,
        ];

        if ($tenantJobsHasTenantCompanyId) {
            $jobAttributes['tenant_company_id'] = $company?->id;
        }

        if ($packageSelectionReady && ! empty($validated['tenant_package_id'])) {
            $jobAttributes['tenant_package_id'] = $validated['tenant_package_id'];
        }

        if ($tenantJobsHasCompanyLogoPath) {
            $jobAttributes['company_logo_path'] = $companyLogoPath;
        }

        $job = TenantJob::create($jobAttributes);

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

        if (Schema::hasTable('tenant_companies')) {
            $job->loadMissing('company');
        }

        $companyVacancyCount = null;

        if (Schema::hasColumn('tenant_jobs', 'tenant_company_id') && $job->tenant_company_id) {
            $companyVacancyCount = TenantJob::query()
                ->where('tenant_id', tenant('id'))
                ->where('tenant_company_id', $job->tenant_company_id)
                ->where('status', TenantJob::STATUS_PUBLISHED)
                ->count();
        } elseif ($job->company_name) {
            $companyVacancyCount = TenantJob::query()
                ->where('tenant_id', tenant('id'))
                ->where('company_name', $job->company_name)
                ->where('status', TenantJob::STATUS_PUBLISHED)
                ->count();
        }

        return view('tenant.job-show', [
            'tenant' => tenant(),
            'job' => $job,
            'companyVacancyCount' => $companyVacancyCount,
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
            $cvPath = PublicUploadStorage::store($request->file('cv'), 'applications', tenant('id'));
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
            'sectors' => $filterOptions['sectors'],
            'organizationTypes' => $filterOptions['organizationTypes'],
            'departmentCounts' => $filterOptions['departmentCounts'],
            'employmentTypeCounts' => $filterOptions['employmentTypeCounts'],
            'sectorCounts' => $filterOptions['sectorCounts'],
            'organizationTypeCounts' => $filterOptions['organizationTypeCounts'],
            'totalJobs' => TenantJob::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', TenantJob::STATUS_PUBLISHED)
                ->count(),
            'focus' => 'contact',
        ]);
    }

    private function jobsQuery(Request $request)
    {
        $query = TenantJob::query();
        $companyFiltersReady = Schema::hasTable('tenant_companies')
            && Schema::hasColumn('tenant_jobs', 'tenant_company_id');
        $companySectorReady = $companyFiltersReady && Schema::hasColumn('tenant_companies', 'sector');
        $companyOrganizationTypeReady = $companyFiltersReady && Schema::hasColumn('tenant_companies', 'organization_type');

        if (Schema::hasTable('tenant_companies')) {
            $query->with('company');
        }

        return $query
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
            ->when($request->filled('sector') && $companySectorReady, function ($query) use ($request): void {
                $query->whereHas('company', fn ($companyQuery) => $this->whereCompanyClassificationMatches($companyQuery, 'sector', $this->selectedFilterValues($request, 'sector')));
            })
            ->when($request->filled('organization_type') && $companyOrganizationTypeReady, function ($query) use ($request): void {
                $query->whereHas('company', fn ($companyQuery) => $this->whereCompanyClassificationMatches($companyQuery, 'organization_type', $this->selectedFilterValues($request, 'organization_type')));
            })
            ->latest('published_at');
    }

    private function postJobPackages(string $tenantId)
    {
        if (! $this->packageSelectionReady()) {
            return collect();
        }

        return TenantPackage::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    private function pricingPackages(string $tenantId)
    {
        if (! Schema::hasTable('tenant_packages')) {
            return collect();
        }

        return TenantPackage::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('price')
            ->orderBy('name')
            ->get();
    }

    private function packageSelectionReady(): bool
    {
        return Schema::hasTable('tenant_packages')
            && Schema::hasTable('tenant_jobs')
            && Schema::hasColumn('tenant_jobs', 'tenant_package_id');
    }

    /**
     * @return array{departments: Collection<int, string>, locations: Collection<int, string>, employmentTypes: Collection<int, string>, sectors: Collection<int, string>, organizationTypes: Collection<int, string>, departmentCounts: array<string, int>, employmentTypeCounts: array<string, int>, sectorCounts: array<string, int>, organizationTypeCounts: array<string, int>}
     */
    private function filterOptions(string $tenantId): array
    {
        $baseQuery = TenantJob::query()
            ->where('tenant_jobs.tenant_id', $tenantId)
            ->where('tenant_jobs.status', TenantJob::STATUS_PUBLISHED);
        $companyFiltersReady = Schema::hasTable('tenant_companies')
            && Schema::hasColumn('tenant_jobs', 'tenant_company_id');
        $companySectorReady = $companyFiltersReady && Schema::hasColumn('tenant_companies', 'sector');
        $companyOrganizationTypeReady = $companyFiltersReady && Schema::hasColumn('tenant_companies', 'organization_type');
        $sortClassificationValues = fn (Collection $values): Collection => $values
            ->filter()
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->sort(fn (string $first, string $second): int => strcasecmp($first, $second))
            ->values();
        $companyOptions = function (string $column) use ($baseQuery, $sortClassificationValues): Collection {
            return $sortClassificationValues((clone $baseQuery)
                ->join('tenant_companies', 'tenant_jobs.tenant_company_id', '=', 'tenant_companies.id')
                ->whereNotNull("tenant_companies.{$column}")
                ->where("tenant_companies.{$column}", '!=', '')
                ->pluck("tenant_companies.{$column}")
                ->flatMap(fn (mixed $value): array => TenantCompany::classificationValuesFromRaw($value)));
        };
        $companyOptionCounts = function (string $column) use ($baseQuery): array {
            $counts = [];

            (clone $baseQuery)
                ->join('tenant_companies', 'tenant_jobs.tenant_company_id', '=', 'tenant_companies.id')
                ->whereNotNull("tenant_companies.{$column}")
                ->where("tenant_companies.{$column}", '!=', '')
                ->pluck("tenant_companies.{$column}")
                ->each(function (mixed $value) use (&$counts): void {
                    foreach (TenantCompany::classificationValuesFromRaw($value) as $classificationValue) {
                        $counts[$classificationValue] = ($counts[$classificationValue] ?? 0) + 1;
                    }
                });

            ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);

            return $counts;
        };

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
            'sectors' => $companySectorReady ? $companyOptions('sector') : collect(),
            'organizationTypes' => $companyOrganizationTypeReady ? $companyOptions('organization_type') : collect(),
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
            'sectorCounts' => $companySectorReady ? $companyOptionCounts('sector') : [],
            'organizationTypeCounts' => $companyOrganizationTypeReady ? $companyOptionCounts('organization_type') : [],
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

    private function whereCompanyClassificationMatches($query, string $column, array $values): void
    {
        $query->where(function ($query) use ($column, $values): void {
            foreach ($values as $value) {
                $jsonNeedle = json_encode($value, JSON_UNESCAPED_UNICODE);

                $query->orWhere($column, $value)
                    ->orWhere($column, 'like', '%'.$jsonNeedle.'%');
            }
        });
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

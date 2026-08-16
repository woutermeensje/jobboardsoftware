<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\TenantJob;
use App\Models\TenantPackage;
use App\Support\AdminActionNotifier;
use App\Support\JobTypeOptions;
use App\Support\RichTextSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenants = $user->ownedTenants()
            ->with(['domains'])
            ->withCount(['jobs', 'applications'])
            ->latest()
            ->get();

        $tenantIds = $tenants->pluck('id');

        return view('client.dashboard', [
            'user' => $user,
            'tenants' => $tenants,
            'domains' => Domain::query()->whereIn('tenant_id', $tenantIds)->latest()->take(6)->get(),
            'jobs' => TenantJob::query()->whereIn('tenant_id', $tenantIds)->latest()->take(6)->get(),
            'applications' => JobApplication::query()->whereIn('tenant_id', $tenantIds)->latest()->take(6)->get(),
        ]);
    }

    public function domains(Request $request): View
    {
        $user = $request->user();
        $tenants = $user->ownedTenants()
            ->with(['domains'])
            ->latest()
            ->get();

        return view('client.domains', [
            'user' => $user,
            'tenants' => $tenants,
            'domains' => Domain::with('tenant')
                ->whereIn('tenant_id', $tenants->pluck('id'))
                ->latest()
                ->get(),
            'dnsTarget' => $this->dnsTarget(),
        ]);
    }

    public function storeDomain(Request $request, AdminActionNotifier $notifier): RedirectResponse
    {
        $request->merge([
            'domain' => $this->normalizeDomain((string) $request->input('domain')),
        ]);

        $centralDomains = $this->centralDomains();

        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::notIn($centralDomains),
                Rule::unique('domains', 'domain'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isValidDomain((string) $value)) {
                        $fail('Enter a valid domain, for example careers.example.com.');
                    }
                },
            ],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        $makePrimary = (bool) ($validated['is_primary'] ?? false) || ! $tenant->domains()->exists();

        if ($makePrimary) {
            $tenant->domains()->update(['is_primary' => false]);
        }

        $verificationToken = Str::random(40);
        $domain = $tenant->domains()->create([
            'domain' => $validated['domain'],
            'is_primary' => $makePrimary,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
            'verification_token' => $verificationToken,
            'verification_payload' => [
                'type' => 'CNAME',
                'host' => $validated['domain'],
                'value' => $this->dnsTarget(),
                'txt_name' => '_jobboardsoftware-verification.'.$validated['domain'],
                'txt_value' => 'jobboardsoftware-site-verification='.$verificationToken,
            ],
        ]);

        $notifier->notify('New domain connected', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'domain' => $domain->domain,
            'primary' => $domain->is_primary,
        ], $request->user());

        return redirect()
            ->route('client.domains.index')
            ->with('status', 'Domain connected. Add the DNS records below to complete verification.');
    }

    public function verifyDomain(Request $request, Domain $domain): RedirectResponse
    {
        abort_unless(
            $request->user()->ownedTenants()->whereKey($domain->tenant_id)->exists(),
            404,
        );

        $verified = $domain->checkDnsVerification();

        return back()->with(
            'status',
            $verified
                ? 'DNS verification succeeded. SSL can now be activated.'
                : 'DNS records were not found yet. Check the values below and try again.',
        );
    }

    public function section(Request $request, string $section): View
    {
        $sectionData = $this->sections()[$section] ?? [
            'title' => str($section)->headline()->toString(),
            'description' => 'This custom client dashboard section is ready to be designed.',
        ];

        return view('client.section', [
            'user' => $request->user(),
            'section' => $sectionData,
        ]);
    }

    public function companies(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();

        return view('client.companies', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'companies' => TenantCompany::query()
                ->with('tenant')
                ->whereIn('tenant_id', $tenants->pluck('id'))
                ->latest()
                ->get(),
        ]);
    }

    public function createCompany(Request $request): View
    {
        return view('client.create-company', [
            'user' => $request->user(),
            'tenants' => $request->user()
                ->ownedTenants()
                ->latest()
                ->get(),
        ]);
    }

    public function createJob(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();
        $tenantIds = $tenants->pluck('id');
        $companyTableReady = Schema::hasTable('tenant_companies');

        return view('client.create-job', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'companies' => $companyTableReady
                ? TenantCompany::query()
                    ->whereIn('tenant_id', $tenantIds)
                    ->orderBy('name')
                    ->get()
                : collect(),
            'categories' => TenantJob::query()
                ->whereIn('tenant_id', $tenantIds)
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),
            'jobTypes' => $tenants
                ->flatMap(fn (Tenant $tenant): array => JobTypeOptions::allForTenant($tenant))
                ->unique(fn (string $jobType): string => mb_strtolower($jobType))
                ->values(),
        ]);
    }

    public function storeJob(Request $request): RedirectResponse
    {
        $companyTableReady = Schema::hasTable('tenant_companies');

        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'tenant_company_id' => ['nullable', 'integer'],
            'company_name' => [Rule::requiredIf(! $request->filled('tenant_company_id')), 'nullable', 'string', 'max:255'],
            'company_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'max:80'],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:3000'],
            'description' => ['required', 'string', 'max:10000'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in([TenantJob::STATUS_DRAFT, TenantJob::STATUS_PUBLISHED])],
            'closes_at' => ['nullable', 'date'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        if (! in_array($validated['employment_type'], JobTypeOptions::allForTenant($tenant), true)) {
            return back()
                ->withErrors(['employment_type' => 'Select a job type that belongs to this environment.'])
                ->withInput();
        }

        $company = null;

        if ($companyTableReady && ! empty($validated['tenant_company_id'])) {
            $company = TenantCompany::query()
                ->where('tenant_id', $tenant->id)
                ->find($validated['tenant_company_id']);

            if (! $company) {
                return back()
                    ->withErrors(['tenant_company_id' => 'Select a company that belongs to this environment.'])
                    ->withInput();
            }
        }

        $companyName = $company?->name ?? $validated['company_name'] ?? null;

        if (! $companyName) {
            return back()
                ->withErrors(['company_name' => 'Enter a company name or select an existing company.'])
                ->withInput();
        }

        $description = RichTextSanitizer::sanitize($validated['description']);

        if ($description === null) {
            return back()
                ->withErrors(['description' => 'Enter a job description.'])
                ->withInput();
        }

        $intro = RichTextSanitizer::sanitize($validated['intro'] ?? null);
        $tenantJobsHasTenantCompanyId = Schema::hasColumn('tenant_jobs', 'tenant_company_id');
        $tenantJobsHasCompanyName = Schema::hasColumn('tenant_jobs', 'company_name');
        $tenantJobsHasCompanyLogoPath = Schema::hasColumn('tenant_jobs', 'company_logo_path');
        $tenantJobsHasContactName = Schema::hasColumn('tenant_jobs', 'contact_name');
        $tenantJobsHasContactEmail = Schema::hasColumn('tenant_jobs', 'contact_email');
        $tenantJobsHasContactPhone = Schema::hasColumn('tenant_jobs', 'contact_phone');
        $tenantJobsHasSubmittedByUserId = Schema::hasColumn('tenant_jobs', 'submitted_by_user_id');
        $companyLogoPath = $tenantJobsHasCompanyLogoPath && $request->hasFile('company_logo')
            ? $request->file('company_logo')->store('company-logos', 'public')
            : ($tenantJobsHasCompanyLogoPath ? $company?->logo_path : null);

        $jobAttributes = [
            'tenant_id' => $tenant->id,
            'title' => $validated['title'],
            'slug' => $this->uniqueJobSlug($tenant, $validated['title']),
            'department' => $validated['category'],
            'location' => $validated['location'],
            'employment_type' => $validated['employment_type'],
            'salary_range' => $validated['salary_range'] ?? null,
            'intro' => $intro,
            'description' => $description,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === TenantJob::STATUS_PUBLISHED ? now() : null,
            'closes_at' => $validated['closes_at'] ?? null,
        ];

        if ($tenantJobsHasTenantCompanyId) {
            $jobAttributes['tenant_company_id'] = $company?->id;
        }

        if ($tenantJobsHasCompanyName) {
            $jobAttributes['company_name'] = $companyName;
        }

        if ($tenantJobsHasCompanyLogoPath) {
            $jobAttributes['company_logo_path'] = $companyLogoPath;
        }

        if ($tenantJobsHasContactName) {
            $jobAttributes['contact_name'] = $validated['contact_name'] ?? $company?->contact_name ?? $request->user()->name;
        }

        if ($tenantJobsHasContactEmail) {
            $jobAttributes['contact_email'] = $validated['contact_email'] ?? $company?->contact_email ?? $request->user()->email;
        }

        if ($tenantJobsHasContactPhone) {
            $jobAttributes['contact_phone'] = $validated['contact_phone'] ?? $company?->contact_phone ?? null;
        }

        if ($tenantJobsHasSubmittedByUserId) {
            $jobAttributes['submitted_by_user_id'] = $request->user()->id;
        }

        TenantJob::query()->create($jobAttributes);

        return redirect()
            ->route('client.jobs.create')
            ->with('status', 'Job created.');
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'organization_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('company-logos', 'public')
            : null;
        $contactName = trim(collect([
            $validated['contact_first_name'] ?? null,
            $validated['contact_last_name'] ?? null,
        ])->filter()->implode(' '));

        TenantCompany::query()->create([
            'tenant_id' => $tenant->id,
            'organization_name' => $validated['organization_name'],
            'name' => $validated['name'],
            'slug' => $this->uniqueCompanySlug($tenant, $validated['name']),
            'contact_first_name' => $validated['contact_first_name'] ?? null,
            'contact_last_name' => $validated['contact_last_name'] ?? null,
            'contact_name' => $contactName !== '' ? $contactName : null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'logo_path' => $logoPath,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('client.companies.index')
            ->with('status', 'Company created.');
    }

    public function jobTypes(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();

        return view('client.job-types', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'defaultJobTypes' => JobTypeOptions::defaults(),
            'jobTypesByTenant' => $tenants->mapWithKeys(fn (Tenant $tenant): array => [
                $tenant->id => JobTypeOptions::customForTenant($tenant),
            ]),
        ]);
    }

    public function storeJobType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'name' => ['required', 'string', 'max:80'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        $name = JobTypeOptions::normalizeName((string) $validated['name']);

        if ($name === '') {
            return back()
                ->withErrors(['name' => 'Enter a job type name.'])
                ->withInput();
        }

        $customJobTypes = JobTypeOptions::customForTenant($tenant);
        $existingJobTypes = collect(JobTypeOptions::defaults())
            ->merge($customJobTypes)
            ->map(fn (string $type): string => mb_strtolower($type))
            ->all();

        if (in_array(mb_strtolower($name), $existingJobTypes, true)) {
            return back()
                ->withErrors(['name' => 'This job type already exists for this environment.'])
                ->withInput();
        }

        $settings = $tenant->settings ?? [];
        $settings['custom_job_types'] = collect($customJobTypes)
            ->push($name)
            ->sortBy(fn (string $type): string => mb_strtolower($type))
            ->values()
            ->all();

        $tenant->forceFill(['settings' => $settings])->save();

        return redirect()
            ->route('client.jobs-settings.job-type')
            ->with('status', 'Job type added.');
    }

    public function packages(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();
        $packageTableReady = Schema::hasTable('tenant_packages');

        return view('client.packages', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'packageTableReady' => $packageTableReady,
            'packages' => $packageTableReady
                ? TenantPackage::query()
                    ->with('tenant')
                    ->whereIn('tenant_id', $tenants->pluck('id'))
                    ->latest()
                    ->get()
                : collect(),
        ]);
    }

    public function createPackage(Request $request): View
    {
        return view('client.create-package', [
            'user' => $request->user(),
            'tenants' => $request->user()
                ->ownedTenants()
                ->latest()
                ->get(),
            'packageTableReady' => Schema::hasTable('tenant_packages'),
        ]);
    }

    public function storePackage(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('tenant_packages')) {
            return redirect()
                ->route('client.packages.index')
                ->withErrors(['packages' => 'Package storage is not ready yet. Run the latest database migrations before adding packages.']);
        }

        $request->merge([
            'currency' => Str::upper(trim((string) $request->input('currency'))),
        ]);

        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tenant_packages', 'name')->where(fn ($query) => $query->where('tenant_id', $request->input('tenant_id'))),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'online_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        TenantPackage::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'online_days' => $validated['online_days'],
        ]);

        return redirect()
            ->route('client.packages.index')
            ->with('status', 'Package added.');
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function sections(): array
    {
        return [
            'environments' => [
                'title' => 'Environments',
                'description' => 'Design the custom overview and settings for a user job board environment here.',
            ],
            'create-environment' => [
                'title' => 'Create environment',
                'description' => 'Build the custom flow for creating a new job board environment here.',
                'layout' => 'form',
                'aside_title' => 'Environment setup',
                'aside_description' => 'Connect the environment to the brand, domain and job settings that belong to this job board.',
            ],
            'jobs' => [
                'title' => 'Jobs',
                'description' => 'Build the custom job management screens here.',
            ],
            'create-job' => [
                'title' => 'Create job',
                'description' => 'Build the custom job creation form here.',
                'layout' => 'form',
                'aside_title' => 'Job setup',
                'aside_description' => 'Use the selected environment, category and job type to keep every vacancy consistent on the job board.',
            ],
            'domains' => [
                'title' => 'Domains',
                'description' => 'Build the custom domain connection and DNS verification screens here.',
            ],
            'create-domain' => [
                'title' => 'Add domain',
                'description' => 'Build the custom form for connecting a domain here.',
                'layout' => 'form',
                'aside_title' => 'DNS setup',
                'aside_description' => 'Add the domain first, then verify the generated DNS records before routing traffic to the job board.',
            ],
            'applications' => [
                'title' => 'Applications',
                'description' => 'Build the custom applicant management screens here.',
            ],
            'billing' => [
                'title' => 'Billing',
                'description' => 'Build the custom billing and package management screens here.',
            ],
            'marketing' => [
                'title' => 'Marketing',
                'description' => 'Build the custom marketing overview here.',
            ],
            'landingpagina' => [
                'title' => 'Landing pages',
                'description' => 'Build the custom landing page editor here.',
                'layout' => 'form',
                'aside_title' => 'Landing page setup',
                'aside_description' => 'Landing pages should stay connected to the selected environment and campaign goal.',
            ],
            'socials' => [
                'title' => 'Social channels',
                'description' => 'Build the custom social channel settings here.',
                'layout' => 'form',
                'aside_title' => 'Channel setup',
                'aside_description' => 'Keep social channel settings grouped per environment so campaigns remain easy to manage.',
            ],
            'jobs-settings' => [
                'title' => 'Jobs settings',
                'description' => 'Build the custom job settings overview here.',
            ],
            'sector' => [
                'title' => 'Sectors',
                'description' => 'Build the custom sector management screen here.',
                'layout' => 'form',
                'aside_title' => 'Sector settings',
                'aside_description' => 'Sectors help group categories and job content across an environment.',
            ],
            'categorie' => [
                'title' => 'Categories',
                'description' => 'Build the custom category management screen here.',
                'layout' => 'form',
                'aside_title' => 'Category settings',
                'aside_description' => 'Categories are used in job filters and vacancy organization on tenant job boards.',
            ],
            'job-type' => [
                'title' => 'Job types',
                'description' => 'Manage the employment types available for jobs in each environment.',
            ],
            'organization-type' => [
                'title' => 'Organization types',
                'description' => 'Build the custom organization type management screen here.',
                'layout' => 'form',
                'aside_title' => 'Organization settings',
                'aside_description' => 'Organization types make company and employer profiles easier to classify.',
            ],
            'companies' => [
                'title' => 'Companies',
                'description' => 'Build the custom company management overview here.',
            ],
            'create-company' => [
                'title' => 'Create company',
                'description' => 'Build the custom company creation form here.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function centralDomains(): array
    {
        return collect(config('tenancy.central_domains', []))
            ->map(fn (string $domain): string => $this->normalizeDomain($domain))
            ->filter()
            ->values()
            ->all();
    }

    private function dnsTarget(): string
    {
        $centralDomain = collect($this->centralDomains())
            ->first(fn (string $domain): bool => ! in_array($domain, ['127.0.0.1', 'localhost'], true));

        if ($centralDomain) {
            return $centralDomain;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return is_string($appHost) && $appHost !== '' ? $appHost : 'jobboardsoftware.co';
    }

    private function normalizeDomain(string $value): string
    {
        $value = trim(Str::lower($value));

        if ($value === '') {
            return '';
        }

        $url = Str::contains($value, '://') ? $value : 'https://'.$value;
        $host = parse_url($url, PHP_URL_HOST);
        $domain = is_string($host) ? $host : $value;
        $domain = trim($domain, " \t\n\r\0\x0B.");

        if (function_exists('idn_to_ascii')) {
            $asciiDomain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if (is_string($asciiDomain)) {
                $domain = $asciiDomain;
            }
        }

        return Str::lower($domain);
    }

    private function uniqueCompanySlug(Tenant $tenant, string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (TenantCompany::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueJobSlug(Tenant $tenant, string $title): string
    {
        $base = Str::slug($title) ?: 'job';
        $slug = $base;
        $suffix = 2;

        while (TenantJob::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function isValidDomain(string $domain): bool
    {
        if ($domain === '' || strlen($domain) > 255 || ! str_contains($domain, '.')) {
            return false;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }
}

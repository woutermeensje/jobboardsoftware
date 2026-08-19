<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\JobAlert;
use App\Models\JobApplication;
use App\Models\LandingPage;
use App\Models\NewsletterSubscriber;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\TenantJob;
use App\Models\TenantPackage;
use App\Support\AdminActionNotifier;
use App\Support\CountryOptions;
use App\Support\JobTypeOptions;
use App\Support\LaravelCloudApiException;
use App\Support\LaravelCloudClient;
use App\Support\PublicUploadStorage;
use App\Support\RichTextSanitizer;
use App\Support\TenantOptionSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

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

    public function storeDomain(Request $request, AdminActionNotifier $notifier, LaravelCloudClient $cloud): RedirectResponse
    {
        $request->merge([
            'domain' => $this->normalizeDomain((string) $request->input('domain')),
            'cloudflare_strategy' => $request->input('cloudflare_strategy', Domain::CLOUDFLARE_NONE),
            'verification_method' => $request->input('verification_method', Domain::VERIFICATION_REAL_TIME),
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
            'www_redirect' => ['nullable', Rule::in([Domain::WWW_TO_ROOT, Domain::ROOT_TO_WWW])],
            'wildcard_enabled' => ['nullable', 'boolean'],
            'allow_downtime' => ['nullable', 'boolean'],
            'cloudflare_strategy' => ['required', Rule::in([
                Domain::CLOUDFLARE_NONE,
                Domain::CLOUDFLARE_DNS,
                Domain::CLOUDFLARE_DNS_PROXY,
            ])],
            'verification_method' => ['required', Rule::in([
                Domain::VERIFICATION_REAL_TIME,
                Domain::VERIFICATION_PRE_VERIFICATION,
            ])],
        ]);

        $validated['wildcard_enabled'] = $request->boolean('wildcard_enabled');
        $validated['allow_downtime'] = $request->has('allow_downtime')
            ? $request->boolean('allow_downtime')
            : true;
        $validated['www_redirect'] = $validated['www_redirect'] ?? null;

        if (! $validated['allow_downtime']) {
            $validated['verification_method'] = Domain::VERIFICATION_PRE_VERIFICATION;
        }

        if ($validated['wildcard_enabled']
            && $validated['cloudflare_strategy'] !== Domain::CLOUDFLARE_DNS_PROXY
            && $validated['verification_method'] !== Domain::VERIFICATION_PRE_VERIFICATION) {
            return back()
                ->withErrors(['verification_method' => 'Wildcard domains need pre-verification unless the domain is already proxied through Cloudflare.'])
                ->withInput();
        }

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        $cloudResponse = null;

        if ($cloud->enabled()) {
            try {
                $cloudResponse = $cloud->createDomain($validated['domain'], [
                    'www_redirect' => $validated['www_redirect'],
                    'wildcard_enabled' => $validated['wildcard_enabled'],
                    'allow_downtime' => $validated['allow_downtime'],
                    'cloudflare_strategy' => $validated['cloudflare_strategy'],
                    'verification_method' => $validated['verification_method'],
                ]);
            } catch (LaravelCloudApiException $exception) {
                return back()
                    ->withErrors(['domain' => 'Laravel Cloud could not connect this domain: '.$exception->getMessage()])
                    ->withInput();
            }
        }

        // Only one replacement can be pending at a time.
        $tenant->domains()->where('is_primary', false)->delete();

        $makePrimary = $cloudResponse === null || ! $tenant->domains()->exists();

        if ($makePrimary) {
            $tenant->domains()->update(['is_primary' => false]);
        }

        $verificationToken = Str::random(40);
        $domain = $tenant->domains()->create([
            'domain' => $validated['domain'],
            'is_primary' => $makePrimary,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
            'cloud_environment_id' => $cloudResponse ? $cloud->environmentId() : null,
            'cloudflare_strategy' => $validated['cloudflare_strategy'],
            'verification_method' => $validated['verification_method'],
            'www_redirect' => $validated['www_redirect'],
            'wildcard_enabled' => $validated['wildcard_enabled'],
            'allow_downtime' => $validated['allow_downtime'],
            'verification_token' => $cloudResponse ? null : $verificationToken,
            'verification_payload' => $cloudResponse
                ? ['provider' => 'laravel_cloud']
                : [
                    'type' => 'CNAME',
                    'host' => $validated['domain'],
                    'value' => $this->dnsTarget(),
                    'txt_name' => '_jobboardsoftware-verification.'.$validated['domain'],
                    'txt_value' => 'jobboardsoftware-site-verification='.$verificationToken,
                ],
        ]);

        if ($cloudResponse) {
            $domain->syncFromLaravelCloud($cloudResponse, $cloud->environmentId());
            $this->promoteDomainIfReady($domain->refresh());
        }

        $notifier->notify('New domain connected', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'domain' => $domain->domain,
            'primary' => $domain->is_primary,
        ], $request->user());

        return redirect()
            ->route('client.domains.index')
            ->with('status', $makePrimary
                ? 'Domain connected. Add the DNS records below to complete verification.'
                : 'Domain connected. Once DNS verification succeeds, this will replace your current domain. Add the DNS records below to complete verification.');
    }

    public function verifyDomain(Request $request, Domain $domain, LaravelCloudClient $cloud): RedirectResponse
    {
        abort_unless(
            $request->user()->ownedTenants()->whereKey($domain->tenant_id)->exists(),
            404,
        );

        if ($domain->usesLaravelCloud()) {
            if (! $cloud->enabled() || ! $domain->cloud_domain_id) {
                return back()->with('status', 'Laravel Cloud API is not configured for this domain yet.');
            }

            try {
                $domain->syncFromLaravelCloud($cloud->verifyDomain($domain->cloud_domain_id), $cloud->environmentId());
            } catch (LaravelCloudApiException $exception) {
                return back()->with('status', 'Laravel Cloud could not verify this domain: '.$exception->getMessage());
            }

            $verified = $this->promoteDomainIfReady($domain->refresh());

            return back()->with(
                'status',
                $verified
                    ? 'Laravel Cloud verification succeeded and this domain is now live.'
                    : 'Laravel Cloud is still waiting for DNS verification. Check the records below and try again.',
            );
        }

        $verified = $domain->checkDnsVerification();

        if ($verified) {
            $this->promoteDomainIfReady($domain);
        }

        return back()->with(
            'status',
            $verified
                ? 'DNS verification succeeded and this domain now replaces your previous domain. SSL can now be activated.'
                : 'DNS records were not found yet. Check the values below and try again.',
        );
    }

    public function environments(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->with('domains')
            ->withCount(['jobs', 'applications'])
            ->latest()
            ->get();

        return view('client.environments', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'activeTenantId' => $request->user()->active_tenant_id,
        ]);
    }

    public function createEnvironment(Request $request): View
    {
        return view('client.create-environment', [
            'user' => $request->user(),
            'baseDomain' => $this->dnsTarget(),
        ]);
    }

    public function storeEnvironment(Request $request): RedirectResponse
    {
        $baseDomain = $this->dnsTarget();

        $request->merge([
            'subdomain' => Str::lower(trim((string) $request->input('subdomain'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',
                Rule::unique('tenants', 'id'),
                Rule::unique('tenants', 'slug'),
                function (string $attribute, mixed $value, \Closure $fail) use ($baseDomain): void {
                    if (Domain::query()->where('domain', $value.'.'.$baseDomain)->exists()) {
                        $fail('This subdomain is already taken.');
                    }
                },
            ],
        ], [
            'subdomain.regex' => 'Use only lowercase letters, numbers and hyphens.',
        ]);

        $tenantPlan = $request->user()->billingPlan?->key ?? Tenant::PLAN_STARTER;

        $tenant = Tenant::create([
            'id' => $validated['subdomain'],
            'owner_user_id' => $request->user()->id,
            'name' => $validated['name'],
            'slug' => $validated['subdomain'],
            'plan' => $tenantPlan,
            'status' => Tenant::STATUS_TRIAL,
            'billing_status' => 'trial',
            'onboarding_step' => 'domain',
            'trial_ends_at' => now()->addDays((int) config('billing.free_trial_days', 14)),
            'settings' => [
                'brand_name' => $validated['name'],
                'primary_color' => '#2f5f80',
                'secondary_color' => '#d99a5b',
                'accent_color' => '#d99a5b',
                'homepage_title' => 'Search all jobs',
                'homepage_subtitle' => 'Jobs, internships and roles at '.$validated['name'].'.',
                'intro' => 'Find your next role at '.$validated['name'].'.',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => $validated['subdomain'].'.'.$baseDomain,
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
            'verified_at' => now(),
            'ssl_issued_at' => now(),
        ]);

        if ($request->user()->active_tenant_id === null) {
            $request->user()->forceFill(['active_tenant_id' => $tenant->id])->save();
        }

        return redirect()
            ->route('client.environments.index')
            ->with('status', 'Job board environment created. It is live at '.$validated['subdomain'].'.'.$baseDomain.'.');
    }

    public function activateEnvironment(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->owner_user_id === $request->user()->id, 404);

        $request->user()->forceFill(['active_tenant_id' => $tenant->id])->save();

        return redirect()
            ->route('client.environments.index')
            ->with('status', $tenant->name.' is now your active environment.');
    }

    public function destroyEnvironment(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->owner_user_id === $request->user()->id, 404);

        $wasActive = $request->user()->active_tenant_id === $tenant->id;
        $tenantName = $tenant->name;
        $tenant->delete();

        if ($wasActive) {
            $nextTenantId = $request->user()->ownedTenants()->oldest()->value('id');
            $request->user()->forceFill(['active_tenant_id' => $nextTenantId])->save();
        }

        return redirect()
            ->route('client.environments.index')
            ->with('status', $tenantName.' has been deleted.');
    }

    public function section(Request $request, string $section): View
    {
        if (TenantOptionSettings::has($section)) {
            return $this->jobSettingOptions($request, $section);
        }

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

    public function landingPages(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->with('domains')
            ->latest()
            ->get();
        $tenantIds = $tenants->pluck('id');

        $customPages = LandingPage::query()
            ->with('tenant.domains')
            ->whereIn('tenant_id', $tenantIds)
            ->latest()
            ->get();

        $pages = collect();

        foreach ($tenants as $tenant) {
            $domain = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->first();
            $baseUrl = $domain ? 'https://'.$domain->domain : null;

            foreach ($this->systemFrontendPages() as $systemPage) {
                $pages->push([
                    'title' => $systemPage['title'],
                    'url' => $baseUrl ? $baseUrl.$systemPage['path'] : null,
                    'tenant_name' => $tenant->name,
                    'type' => 'system',
                    'status' => null,
                    'landing_page' => null,
                ]);
            }
        }

        foreach ($customPages as $customPage) {
            $domain = $customPage->tenant?->domains->firstWhere('is_primary', true) ?? $customPage->tenant?->domains->first();
            $baseUrl = $domain ? 'https://'.$domain->domain : null;

            $pages->push([
                'title' => $customPage->title,
                'url' => $baseUrl ? $baseUrl.'/pages/'.$customPage->slug : null,
                'tenant_name' => $customPage->tenant?->name,
                'type' => 'custom',
                'status' => $customPage->status,
                'landing_page' => $customPage,
            ]);
        }

        return view('client.marketing-landingpagina', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'pages' => $pages,
        ]);
    }

    public function createLandingPage(Request $request): View
    {
        return view('client.create-landingpage', [
            'user' => $request->user(),
            'tenants' => $request->user()->ownedTenants()->latest()->get(),
            'landingPage' => null,
        ]);
    }

    public function editLandingPage(Request $request, LandingPage $landingPage): View
    {
        $this->abortUnlessOwnedLandingPage($request, $landingPage);

        return view('client.create-landingpage', [
            'user' => $request->user(),
            'tenants' => $request->user()->ownedTenants()->latest()->get(),
            'landingPage' => $landingPage,
        ]);
    }

    public function storeLandingPage(Request $request): RedirectResponse
    {
        $this->saveClientLandingPage($request);

        return redirect()
            ->route('client.marketing.landingpagina')
            ->with('status', 'Landing page created.');
    }

    public function updateLandingPage(Request $request, LandingPage $landingPage): RedirectResponse
    {
        $this->abortUnlessOwnedLandingPage($request, $landingPage);
        $this->saveClientLandingPage($request, $landingPage);

        return redirect()
            ->route('client.marketing.landingpagina')
            ->with('status', 'Landing page updated.');
    }

    public function destroyLandingPage(Request $request, LandingPage $landingPage): RedirectResponse
    {
        $this->abortUnlessOwnedLandingPage($request, $landingPage);
        $landingPage->delete();

        return redirect()
            ->route('client.marketing.landingpagina')
            ->with('status', 'Landing page deleted.');
    }

    private function saveClientLandingPage(Request $request, ?LandingPage $landingPage = null): LandingPage
    {
        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:20000'],
            'status' => ['required', Rule::in([LandingPage::STATUS_DRAFT, LandingPage::STATUS_PUBLISHED])],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        $content = RichTextSanitizer::sanitize($validated['content']);

        if ($content === null) {
            return back()
                ->withErrors(['content' => 'Enter page content.'])
                ->withInput()
                ->throwResponse();
        }

        $landingPageAttributes = [
            'tenant_id' => $tenant->id,
            'title' => $validated['title'],
            'meta_description' => $validated['meta_description'] ?? null,
            'content' => $content,
            'status' => $validated['status'],
        ];

        if ($landingPage) {
            $landingPage->update($landingPageAttributes);

            return $landingPage->refresh();
        }

        $landingPageAttributes['slug'] = $this->uniqueLandingPageSlug($tenant, $validated['title']);

        return LandingPage::query()->create($landingPageAttributes);
    }

    public function newsletterSubscribers(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();

        return view('client.newsletter-subscribers', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'subscribers' => NewsletterSubscriber::query()
                ->with('tenant')
                ->whereIn('tenant_id', $tenants->pluck('id'))
                ->latest()
                ->get(),
        ]);
    }

    public function jobAlerts(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();

        return view('client.job-alerts', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'alerts' => JobAlert::query()
                ->with('tenant')
                ->whereIn('tenant_id', $tenants->pluck('id'))
                ->latest()
                ->get(),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('client.settings', [
            'user' => $request->user(),
            'tenants' => $request->user()
                ->ownedTenants()
                ->with('primaryDomain')
                ->latest()
                ->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'homepage_title' => ['nullable', 'string', 'max:255'],
            'homepage_subtitle' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);
        $settings = $tenant->settings ?? [];
        $settings['primary_color'] = Str::upper($validated['primary_color']);
        $settings['secondary_color'] = Str::upper($validated['secondary_color']);
        $settings['accent_color'] = $settings['secondary_color'];
        $settings['homepage_title'] = Str::of($validated['homepage_title'] ?? '')->squish()->toString() ?: null;
        $settings['homepage_subtitle'] = Str::of($validated['homepage_subtitle'] ?? '')->squish()->toString() ?: null;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            try {
                $settings['logo_path'] = PublicUploadStorage::store($logo, 'tenant-logos', $tenant->id);
                unset($settings['logo_url']);
            } catch (RuntimeException) {
                try {
                    $settings['logo_url'] = PublicUploadStorage::dataUrl($logo);
                    unset($settings['logo_path']);
                } catch (RuntimeException) {
                    return back()
                        ->withErrors(['logo' => 'The header logo could not be saved. Check that public upload storage is writable.'])
                        ->withInput();
                }
            }
        }

        $tenant->forceFill(['settings' => $settings])->save();

        return redirect()
            ->route('client.settings')
            ->with('status', 'Settings saved.');
    }

    public function createCompany(Request $request): View
    {
        return view('client.create-company', [
            ...$this->companyFormData($request),
            'company' => null,
        ]);
    }

    public function editCompany(Request $request, TenantCompany $company): View
    {
        $this->abortUnlessOwnedCompany($request, $company);

        return view('client.create-company', [
            ...$this->companyFormData($request, $company),
            'company' => $company->loadMissing('tenant'),
        ]);
    }

    public function createJob(Request $request): View
    {
        return view('client.create-job', [
            ...$this->jobFormData($request),
            'job' => null,
        ]);
    }

    public function editJob(Request $request, TenantJob $job): View
    {
        $this->abortUnlessOwnedJob($request, $job);

        return view('client.create-job', [
            ...$this->jobFormData($request, $job),
            'job' => $job->loadMissing(['tenant', 'company']),
        ]);
    }

    public function jobs(Request $request): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();

        return view('client.jobs', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'jobs' => TenantJob::query()
                ->with(['tenant', 'company'])
                ->whereIn('tenant_id', $tenants->pluck('id'))
                ->whereIn('status', [TenantJob::STATUS_PUBLISHED, TenantJob::STATUS_DRAFT])
                ->latest()
                ->get(),
        ]);
    }

    public function storeJob(Request $request): RedirectResponse
    {
        $this->saveClientJob($request);

        return redirect()
            ->route('client.jobs.index')
            ->with('status', 'Job created.');
    }

    public function updateJob(Request $request, TenantJob $job): RedirectResponse
    {
        $this->abortUnlessOwnedJob($request, $job);
        $this->saveClientJob($request, $job);

        return redirect()
            ->route('client.jobs.edit', $job)
            ->with('status', 'Job updated.');
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $this->saveClientCompany($request);

        return redirect()
            ->route('client.companies.index')
            ->with('status', 'Company created.');
    }

    public function updateCompany(Request $request, TenantCompany $company): RedirectResponse
    {
        $this->abortUnlessOwnedCompany($request, $company);
        $this->saveClientCompany($request, $company);

        return redirect()
            ->route('client.companies.edit', $company)
            ->with('status', 'Company updated.');
    }

    public function jobTypes(Request $request): View
    {
        return $this->jobSettingOptions($request, 'job-type');
    }

    public function storeJobType(Request $request): RedirectResponse
    {
        return $this->storeJobSettingOption($request, 'job-type');
    }

    public function storeJobSettingOption(Request $request, string $optionType): RedirectResponse
    {
        abort_unless(TenantOptionSettings::has($optionType), 404);

        $option = TenantOptionSettings::config($optionType);

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

        $name = TenantOptionSettings::normalizeName((string) $validated['name']);

        if ($name === '') {
            return back()
                ->withErrors(['name' => $option['required_error']])
                ->withInput();
        }

        $customOptions = TenantOptionSettings::customForTenant($tenant, $optionType);
        $existingOptions = collect(TenantOptionSettings::defaults($optionType))
            ->merge($customOptions)
            ->map(fn (string $existingOption): string => mb_strtolower($existingOption))
            ->all();

        if (in_array(mb_strtolower($name), $existingOptions, true)) {
            return back()
                ->withErrors(['name' => $option['duplicate_error']])
                ->withInput();
        }

        $settings = $tenant->settings ?? [];
        $settings[$option['settings_key']] = collect($customOptions)
            ->push($name)
            ->sortBy(fn (string $storedOption): string => mb_strtolower($storedOption))
            ->values()
            ->all();

        $tenant->forceFill(['settings' => $settings])->save();

        return redirect()
            ->route($option['route_name'])
            ->with('status', $option['success']);
    }

    private function jobSettingOptions(Request $request, string $optionType): View
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();
        $option = TenantOptionSettings::config($optionType);

        return view('client.job-setting-options', [
            'user' => $request->user(),
            'tenants' => $tenants,
            'optionType' => $optionType,
            'option' => $option,
            'defaultOptions' => TenantOptionSettings::defaults($optionType),
            'optionsByTenant' => $tenants->mapWithKeys(fn (Tenant $tenant): array => [
                $tenant->id => TenantOptionSettings::customForTenant($tenant, $optionType),
            ]),
        ]);
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
        $packageTableReady = Schema::hasTable('tenant_packages');

        return view('client.create-package', [
            'user' => $request->user(),
            'tenants' => $request->user()
                ->ownedTenants()
                ->latest()
                ->get(),
            'packageTableReady' => $packageTableReady,
            'packageDescriptionColumnReady' => $packageTableReady && Schema::hasColumn('tenant_packages', 'description'),
        ]);
    }

    public function storePackage(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('tenant_packages')) {
            return redirect()
                ->route('client.packages.index')
                ->withErrors(['packages' => 'Package storage is not ready yet. Run the latest database migrations before adding packages.']);
        }

        $this->saveClientPackage($request);

        return redirect()
            ->route('client.packages.index')
            ->with('status', 'Package added.');
    }

    public function editPackage(Request $request, TenantPackage $package): View
    {
        $this->abortUnlessOwnedPackage($request, $package);
        $packageTableReady = Schema::hasTable('tenant_packages');

        return view('client.create-package', [
            'user' => $request->user(),
            'tenants' => $request->user()
                ->ownedTenants()
                ->latest()
                ->get(),
            'package' => $package,
            'packageTableReady' => $packageTableReady,
            'packageDescriptionColumnReady' => $packageTableReady && Schema::hasColumn('tenant_packages', 'description'),
        ]);
    }

    public function updatePackage(Request $request, TenantPackage $package): RedirectResponse
    {
        if (! Schema::hasTable('tenant_packages')) {
            return redirect()
                ->route('client.packages.index')
                ->withErrors(['packages' => 'Package storage is not ready yet. Run the latest database migrations before editing packages.']);
        }

        $this->abortUnlessOwnedPackage($request, $package);
        $request->merge(['tenant_id' => $package->tenant_id]);
        $this->saveClientPackage($request, $package);

        return redirect()
            ->route('client.packages.index')
            ->with('status', 'Package updated.');
    }

    private function saveClientPackage(Request $request, ?TenantPackage $package = null): TenantPackage
    {
        $request->merge([
            'currency' => Str::upper(trim((string) $request->input('currency'))),
        ]);
        $uniquePackageNameRule = Rule::unique('tenant_packages', 'name')
            ->where(fn ($query) => $query->where('tenant_id', $request->input('tenant_id')));

        if ($package) {
            $uniquePackageNameRule->ignore($package->id);
        }

        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                $uniquePackageNameRule,
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'online_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        $packageAttributes = [
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'online_days' => $validated['online_days'],
        ];

        if (Schema::hasColumn('tenant_packages', 'description')) {
            $packageAttributes['description'] = RichTextSanitizer::sanitize($validated['description'] ?? null);
        }

        if ($package) {
            $package->update($packageAttributes);

            return $package->refresh();
        }

        return TenantPackage::query()->create($packageAttributes);
    }

    /**
     * @return array<string, mixed>
     */
    private function companyFormData(Request $request, ?TenantCompany $company = null): array
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();
        $targetTenant = $company?->tenant ?? $tenants->first();
        $sectors = $targetTenant
            ? collect(TenantOptionSettings::allForTenant($targetTenant, 'sector'))
            : collect();
        $organizationTypes = $targetTenant
            ? collect(TenantOptionSettings::allForTenant($targetTenant, 'organization-type'))
            : collect();

        if ($company) {
            $sectors = $sectors->merge($company->sectorValues());
        }

        if ($company) {
            $organizationTypes = $organizationTypes->merge($company->organizationTypeValues());
        }

        return [
            'user' => $request->user(),
            'tenants' => $tenants,
            'companyUrlColumnReady' => Schema::hasColumn('tenant_companies', 'company_url'),
            'sectorColumnReady' => Schema::hasColumn('tenant_companies', 'sector'),
            'organizationTypeColumnReady' => Schema::hasColumn('tenant_companies', 'organization_type'),
            'sectors' => $sectors
                ->filter()
                ->unique(fn (string $sector): string => mb_strtolower($sector))
                ->values(),
            'organizationTypes' => $organizationTypes
                ->filter()
                ->unique(fn (string $organizationType): string => mb_strtolower($organizationType))
                ->values(),
        ];
    }

    private function saveClientCompany(Request $request, ?TenantCompany $company = null): TenantCompany
    {
        $tenantCompaniesHasCompanyUrl = Schema::hasColumn('tenant_companies', 'company_url');
        $tenantCompaniesHasSector = Schema::hasColumn('tenant_companies', 'sector');
        $tenantCompaniesHasOrganizationType = Schema::hasColumn('tenant_companies', 'organization_type');

        if ($tenantCompaniesHasCompanyUrl) {
            $request->merge([
                'company_url' => $this->normalizeExternalUrl($request->input('company_url')),
            ]);
        }

        if ($tenantCompaniesHasSector || $tenantCompaniesHasOrganizationType) {
            $request->merge([
                'sector' => $this->normalizedClassificationInput($request, 'sector'),
                'organization_type' => $this->normalizedClassificationInput($request, 'organization_type'),
            ]);
        }

        $rules = [
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
            'description' => ['nullable', 'string', 'max:10000'],
        ];

        if ($tenantCompaniesHasCompanyUrl) {
            $rules['company_url'] = ['nullable', 'url', 'starts_with:http://,https://', 'max:2048'];
        }

        if ($tenantCompaniesHasSector) {
            $rules['sector'] = ['nullable', 'array'];
            $rules['sector.*'] = ['string', 'max:255'];
        }

        if ($tenantCompaniesHasOrganizationType) {
            $rules['organization_type'] = ['nullable', 'array'];
            $rules['organization_type.*'] = ['string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);

        if ($tenantCompaniesHasSector && ! empty($validated['sector'] ?? [])) {
            $allowedSectors = collect(TenantOptionSettings::allForTenant($tenant, 'sector'));

            if ($company?->tenant_id === $tenant->id) {
                $allowedSectors = $allowedSectors->merge($company->sectorValues());
            }

            $invalidSectors = collect($validated['sector'])
                ->reject(fn (string $sector): bool => $allowedSectors->containsStrict($sector));

            if ($invalidSectors->isNotEmpty()) {
                return back()
                    ->withErrors(['sector' => 'Select a sector that belongs to this environment.'])
                    ->withInput()
                    ->throwResponse();
            }
        }

        if ($tenantCompaniesHasOrganizationType && ! empty($validated['organization_type'] ?? [])) {
            $allowedOrganizationTypes = collect(TenantOptionSettings::allForTenant($tenant, 'organization-type'));

            if ($company?->tenant_id === $tenant->id) {
                $allowedOrganizationTypes = $allowedOrganizationTypes->merge($company->organizationTypeValues());
            }

            $invalidOrganizationTypes = collect($validated['organization_type'])
                ->reject(fn (string $organizationType): bool => $allowedOrganizationTypes->containsStrict($organizationType));

            if ($invalidOrganizationTypes->isNotEmpty()) {
                return back()
                    ->withErrors(['organization_type' => 'Select an organization type that belongs to this environment.'])
                    ->withInput()
                    ->throwResponse();
            }
        }

        $logoPath = $company?->logo_path;

        if ($request->hasFile('logo')) {
            try {
                $logoPath = PublicUploadStorage::store($request->file('logo'), 'company-logos', $tenant->id);
            } catch (RuntimeException) {
                return back()
                    ->withErrors(['logo' => 'The company logo could not be saved. Check that public upload storage is writable.'])
                    ->withInput()
                    ->throwResponse();
            }
        }

        $description = RichTextSanitizer::sanitize($validated['description'] ?? null);
        $contactName = trim(collect([
            $validated['contact_first_name'] ?? null,
            $validated['contact_last_name'] ?? null,
        ])->filter()->implode(' '));
        $shouldRefreshSlug = ! $company
            || $company->tenant_id !== $tenant->id
            || trim($company->name) !== trim($validated['name']);
        $companyAttributes = [
            'tenant_id' => $tenant->id,
            'organization_name' => $validated['organization_name'],
            'name' => $validated['name'],
            'slug' => $shouldRefreshSlug ? $this->uniqueCompanySlug($tenant, $validated['name'], $company) : $company->slug,
            'contact_first_name' => $validated['contact_first_name'] ?? null,
            'contact_last_name' => $validated['contact_last_name'] ?? null,
            'contact_name' => $contactName !== '' ? $contactName : null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'logo_path' => $logoPath,
            'description' => $description,
        ];

        if ($tenantCompaniesHasCompanyUrl) {
            $companyAttributes['company_url'] = $validated['company_url'] ?? null;
        }

        if ($tenantCompaniesHasSector) {
            $companyAttributes['sector'] = TenantCompany::encodeClassificationValues($validated['sector'] ?? []);
        }

        if ($tenantCompaniesHasOrganizationType) {
            $companyAttributes['organization_type'] = TenantCompany::encodeClassificationValues($validated['organization_type'] ?? []);
        }

        if ($company) {
            $company->update($companyAttributes);
            $company->refresh();
        } else {
            $company = TenantCompany::query()->create($companyAttributes);
        }

        $this->syncCompanyJobs($company);

        return $company;
    }

    /**
     * @return array<int, string>
     */
    private function normalizedClassificationInput(Request $request, string $key): array
    {
        return collect((array) $request->input($key, []))
            ->map(fn (mixed $value): string => TenantOptionSettings::normalizeName((string) $value))
            ->filter()
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();
    }

    private function syncCompanyJobs(TenantCompany $company): void
    {
        if (! Schema::hasColumn('tenant_jobs', 'tenant_company_id')) {
            return;
        }

        $updates = [];

        if (Schema::hasColumn('tenant_jobs', 'company_name')) {
            $updates['company_name'] = $company->name;
        }

        if (Schema::hasColumn('tenant_jobs', 'company_logo_path')) {
            $updates['company_logo_path'] = $company->logo_path;
        }

        if (Schema::hasColumn('tenant_jobs', 'company_url')) {
            $updates['company_url'] = $company->company_url;
        }

        if ($updates === []) {
            return;
        }

        TenantJob::query()
            ->where('tenant_id', $company->tenant_id)
            ->where('tenant_company_id', $company->id)
            ->update($updates);
    }

    /**
     * @return array<string, mixed>
     */
    private function jobFormData(Request $request, ?TenantJob $job = null): array
    {
        $tenants = $request->user()
            ->ownedTenants()
            ->latest()
            ->get();
        $tenantIds = $tenants->pluck('id');
        $companyTableReady = Schema::hasTable('tenant_companies');
        $jobTypes = $tenants
            ->flatMap(fn (Tenant $tenant): array => JobTypeOptions::allForTenant($tenant));

        if ($job?->employment_type) {
            $jobTypes->push($job->employment_type);
        }

        return [
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
            'jobTypes' => $jobTypes
                ->filter()
                ->unique(fn (string $jobType): string => mb_strtolower($jobType))
                ->values(),
            'countries' => CountryOptions::all(),
        ];
    }

    private function saveClientJob(Request $request, ?TenantJob $job = null): TenantJob
    {
        $companyTableReady = Schema::hasTable('tenant_companies');
        $isEditing = $job !== null;

        $request->merge([
            'job_url' => $this->normalizeExternalUrl($request->input('job_url')),
        ]);

        $validated = $request->validate([
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('owner_user_id', $request->user()->id)),
            ],
            'tenant_company_id' => [$isEditing ? 'nullable' : 'required', 'integer'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', Rule::in(CountryOptions::codes())],
            'employment_type' => ['required', 'string', 'max:255'],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:3000'],
            'description' => ['required', 'string', 'max:10000'],
            'job_url' => ['nullable', 'url', 'starts_with:http://,https://', 'max:2048'],
            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_last_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in([TenantJob::STATUS_DRAFT, TenantJob::STATUS_PUBLISHED])],
            'closes_at' => ['nullable', 'date'],
        ]);

        $tenant = Tenant::query()
            ->where('owner_user_id', $request->user()->id)
            ->findOrFail($validated['tenant_id']);
        $allowedJobTypes = collect(JobTypeOptions::allForTenant($tenant));

        if ($job?->tenant_id === $tenant->id && $job->employment_type) {
            $allowedJobTypes->push($job->employment_type);
        }

        if (! $allowedJobTypes->containsStrict($validated['employment_type'])) {
            return back()
                ->withErrors(['employment_type' => 'Select a job type that belongs to this environment.'])
                ->withInput()
                ->throwResponse();
        }

        $company = null;

        if ($companyTableReady && ! empty($validated['tenant_company_id'])) {
            $company = TenantCompany::query()
                ->where('tenant_id', $tenant->id)
                ->find($validated['tenant_company_id']);

            if (! $company) {
                return back()
                    ->withErrors(['tenant_company_id' => 'Select a company that belongs to this account.'])
                    ->withInput()
                    ->throwResponse();
            }
        }

        $companyName = $company?->name ?? $validated['company_name'] ?? $job?->company_name ?? null;

        if (! $companyName) {
            return back()
                ->withErrors(['company_name' => 'Enter a company name or select an existing company.'])
                ->withInput()
                ->throwResponse();
        }

        $description = RichTextSanitizer::sanitize($validated['description']);

        if ($description === null) {
            return back()
                ->withErrors(['description' => 'Enter a job description.'])
                ->withInput()
                ->throwResponse();
        }

        $intro = RichTextSanitizer::sanitize($validated['intro'] ?? null);
        $contactName = trim($validated['contact_first_name'].' '.$validated['contact_last_name']);
        $tenantJobsHasTenantCompanyId = Schema::hasColumn('tenant_jobs', 'tenant_company_id');
        $tenantJobsHasCompanyName = Schema::hasColumn('tenant_jobs', 'company_name');
        $tenantJobsHasCompanyLogoPath = Schema::hasColumn('tenant_jobs', 'company_logo_path');
        $tenantJobsHasContactName = Schema::hasColumn('tenant_jobs', 'contact_name');
        $tenantJobsHasContactEmail = Schema::hasColumn('tenant_jobs', 'contact_email');
        $tenantJobsHasContactPhone = Schema::hasColumn('tenant_jobs', 'contact_phone');
        $tenantJobsHasSubmittedByUserId = Schema::hasColumn('tenant_jobs', 'submitted_by_user_id');
        $tenantJobsHasJobUrl = Schema::hasColumn('tenant_jobs', 'job_url');
        $tenantJobsHasCompanyUrl = Schema::hasColumn('tenant_jobs', 'company_url');
        $companyLogoPath = $tenantJobsHasCompanyLogoPath && $request->hasFile('company_logo')
            ? PublicUploadStorage::store($request->file('company_logo'), 'company-logos', $tenant->id)
            : ($company?->logo_path ?? $job?->company_logo_path);
        $companyUrl = $company?->company_url ?? $job?->company_url ?? null;
        $shouldRefreshSlug = ! $job
            || $job->tenant_id !== $tenant->id
            || trim($job->title) !== trim($validated['title']);

        $jobAttributes = [
            'tenant_id' => $tenant->id,
            'title' => $validated['title'],
            'slug' => $shouldRefreshSlug ? $this->uniqueJobSlug($tenant, $validated['title'], $job) : $job->slug,
            'department' => $validated['category'] ?? null,
            'location' => $validated['location'] ?? null,
            'country' => filled($validated['country'] ?? null) ? CountryOptions::normalizeCode($validated['country']) : null,
            'employment_type' => $validated['employment_type'],
            'salary_range' => $validated['salary_range'] ?? null,
            'intro' => $intro,
            'description' => $description,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === TenantJob::STATUS_PUBLISHED ? ($job?->published_at ?? now()) : null,
            'closes_at' => $validated['closes_at'] ?? null,
        ];

        if ($tenantJobsHasJobUrl) {
            $jobAttributes['job_url'] = $validated['job_url'] ?? null;
        }

        if ($tenantJobsHasCompanyUrl) {
            $jobAttributes['company_url'] = $companyUrl;
        }

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
            $jobAttributes['contact_name'] = $contactName;
        }

        if ($tenantJobsHasContactEmail) {
            $jobAttributes['contact_email'] = $validated['contact_email'];
        }

        if ($tenantJobsHasContactPhone) {
            $jobAttributes['contact_phone'] = $validated['contact_phone'] ?? $company?->contact_phone ?? null;
        }

        if ($tenantJobsHasSubmittedByUserId && ! $job) {
            $jobAttributes['submitted_by_user_id'] = $request->user()->id;
        }

        if ($job) {
            $job->update($jobAttributes);

            return $job->refresh();
        }

        return TenantJob::query()->create($jobAttributes);
    }

    private function abortUnlessOwnedJob(Request $request, TenantJob $job): void
    {
        abort_unless(
            $request->user()->ownedTenants()->whereKey($job->tenant_id)->exists(),
            404,
        );
    }

    private function abortUnlessOwnedCompany(Request $request, TenantCompany $company): void
    {
        abort_unless(
            $request->user()->ownedTenants()->whereKey($company->tenant_id)->exists(),
            404,
        );
    }

    private function abortUnlessOwnedPackage(Request $request, TenantPackage $package): void
    {
        abort_unless(
            $request->user()->ownedTenants()->whereKey($package->tenant_id)->exists(),
            404,
        );
    }

    private function abortUnlessOwnedLandingPage(Request $request, LandingPage $landingPage): void
    {
        abort_unless(
            $request->user()->ownedTenants()->whereKey($landingPage->tenant_id)->exists(),
            404,
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function sections(): array
    {
        return [
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
            'billing' => [
                'title' => 'Billing',
                'description' => 'Build the custom billing and package management screens here.',
            ],
            'marketing' => [
                'title' => 'Marketing',
                'description' => 'Build the custom marketing overview here.',
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
                'aside_description' => 'Sectors help classify company profiles across an environment.',
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

    private function promoteDomainIfReady(Domain $domain): bool
    {
        if (! $domain->isReadyForTraffic()) {
            return false;
        }

        $domain->tenant->domains()->whereKeyNot($domain->id)->update(['is_primary' => false]);
        $domain->forceFill(['is_primary' => true])->save();

        return true;
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

    private function normalizeExternalUrl(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Str::contains($value, '://') ? $value : 'https://'.$value;
    }

    private function uniqueCompanySlug(Tenant $tenant, string $name, ?TenantCompany $ignoreCompany = null): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (TenantCompany::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->when($ignoreCompany, fn ($query) => $query->whereKeyNot($ignoreCompany->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueJobSlug(Tenant $tenant, string $title, ?TenantJob $ignoreJob = null): string
    {
        $base = Str::slug($title) ?: 'job';
        $slug = $base;
        $suffix = 2;

        while (TenantJob::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->when($ignoreJob, fn ($query) => $query->whereKeyNot($ignoreJob->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueLandingPageSlug(Tenant $tenant, string $title): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $suffix = 2;

        while (LandingPage::query()->where('tenant_id', $tenant->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<int, array{title: string, path: string}>
     */
    private function systemFrontendPages(): array
    {
        return [
            ['title' => 'Post a job', 'path' => '/post-a-job'],
            ['title' => 'Login', 'path' => '/login'],
            ['title' => 'Sign up', 'path' => '/sign-up'],
        ];
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

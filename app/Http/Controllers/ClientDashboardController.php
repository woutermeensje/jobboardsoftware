<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Support\AdminActionNotifier;
use App\Support\JobTypeOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /**
     * @return array<string, array{title: string, description: string}>
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
            ],
            'jobs' => [
                'title' => 'Jobs',
                'description' => 'Build the custom job management screens here.',
            ],
            'create-job' => [
                'title' => 'Create job',
                'description' => 'Build the custom job creation form here.',
            ],
            'domains' => [
                'title' => 'Domains',
                'description' => 'Build the custom domain connection and DNS verification screens here.',
            ],
            'create-domain' => [
                'title' => 'Add domain',
                'description' => 'Build the custom form for connecting a domain here.',
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
            ],
            'socials' => [
                'title' => 'Social channels',
                'description' => 'Build the custom social channel settings here.',
            ],
            'jobs-settings' => [
                'title' => 'Jobs settings',
                'description' => 'Build the custom job settings overview here.',
            ],
            'sector' => [
                'title' => 'Sectors',
                'description' => 'Build the custom sector management screen here.',
            ],
            'categorie' => [
                'title' => 'Categories',
                'description' => 'Build the custom category management screen here.',
            ],
            'job-type' => [
                'title' => 'Job types',
                'description' => 'Manage the employment types available for jobs in each environment.',
            ],
            'organization-type' => [
                'title' => 'Organization types',
                'description' => 'Build the custom organization type management screen here.',
            ],
            'companies' => [
                'title' => 'Companies',
                'description' => 'Build the custom company management overview here.',
            ],
            'create-company' => [
                'title' => 'Add company',
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

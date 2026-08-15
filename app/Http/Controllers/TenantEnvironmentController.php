<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Tenant;
use App\Support\AdminActionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantEnvironmentController extends Controller
{
    private const PLATFORM_DOMAIN = 'jobboardsoftware.co';

    public function index(Request $request): View
    {
        return view('dashboard.environments.index', [
            'user' => $request->user(),
            'tenants' => $request->user()
                ->ownedTenants()
                ->with('domains')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('tenants', 'slug')],
        ]);

        $slug = Str::slug($validated['slug']);

        $tenant = Tenant::create([
            'id' => $slug,
            'owner_user_id' => $request->user()->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'plan' => $request->user()->billingPlan?->key ?? Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_TRIAL,
            'billing_status' => $request->user()->billing_status ?? 'trial',
            'onboarding_step' => 'jobs',
            'trial_ends_at' => now()->addDays(14),
            'settings' => [
                'brand_name' => $validated['name'],
                'accent_color' => '#2f5f80',
                'intro' => 'View current jobs and apply directly.',
            ],
        ]);

        $tenant->domains()->create($this->subdomainPayload($slug));

        $request->user()->forceFill([
            'onboarding_step' => 'jobs',
        ])->save();

        app(AdminActionNotifier::class)->notify('Environment aangemaakt', [
            'tenant_id' => $tenant->id,
            'tenant_naam' => $tenant->name,
            'slug' => $tenant->slug,
            'pakket' => $tenant->plan,
            'status' => $tenant->status,
            'domein' => $slug.'.'.self::PLATFORM_DOMAIN,
            'onboarding_step' => $request->user()->onboarding_step,
        ], $request->user());

        return redirect()
            ->route('tenant.environments.index')
            ->with('status', 'Your job board environment has been created at '.$slug.'.'.self::PLATFORM_DOMAIN.'.');
    }

    public function storeDomain(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);

        if ($request->filled('domain')) {
            $request->merge(['domain' => $this->normalizeDomain($request->string('domain')->toString())]);
        }

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', Rule::unique('domains', 'domain')],
        ]);

        $tenant->domains()->create($this->domainPayload($validated['domain'], ! $tenant->domains()->exists()));

        $tenant->forceFill([
            'onboarding_step' => 'jobs',
        ])->save();

        $request->user()->forceFill([
            'onboarding_step' => 'jobs',
        ])->save();

        app(AdminActionNotifier::class)->notify('Domein toegevoegd', [
            'tenant_id' => $tenant->id,
            'tenant_naam' => $tenant->name,
            'domein' => $validated['domain'],
            'is_primary' => ! $tenant->domains()->where('domain', '!=', $validated['domain'])->exists(),
            'onboarding_step' => $request->user()->onboarding_step,
        ], $request->user());

        return redirect()
            ->route('tenant.environments.index')
            ->with('status', 'Domain added. Add the CNAME at your DNS provider to complete verification.');
    }

    public function checkDomain(Request $request, Tenant $tenant, Domain $domain): RedirectResponse
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);
        abort_unless($domain->tenant_id === $tenant->id, 404);

        if ($domain->checkDnsVerification()) {
            app(AdminActionNotifier::class)->notify('Domein DNS geverifieerd', [
                'tenant_id' => $tenant->id,
                'tenant_naam' => $tenant->name,
                'domein' => $domain->domain,
                'status' => $domain->status,
                'ssl_status' => $domain->ssl_status,
            ], $request->user());

            return back()->with('status', 'Domain verified. SSL can now be prepared.');
        }

        app(AdminActionNotifier::class)->notify('Domein DNS check mislukt', [
            'tenant_id' => $tenant->id,
            'tenant_naam' => $tenant->name,
            'domein' => $domain->domain,
            'status' => $domain->status,
            'ssl_status' => $domain->ssl_status,
        ], $request->user());

        return back()->with('status', 'DNS records were not found yet. Check the CNAME or TXT value and try again.');
    }

    public function issueSsl(Request $request, Tenant $tenant, Domain $domain): RedirectResponse
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);
        abort_unless($domain->tenant_id === $tenant->id, 404);
        abort_unless(in_array($domain->status, [Domain::STATUS_VERIFIED, Domain::STATUS_ACTIVE], true), 422);

        $domain->activateSsl();

        app(AdminActionNotifier::class)->notify('SSL geactiveerd', [
            'tenant_id' => $tenant->id,
            'tenant_naam' => $tenant->name,
            'domein' => $domain->domain,
            'status' => $domain->status,
            'ssl_status' => $domain->ssl_status,
        ], $request->user());

        return back()->with('status', 'SSL status has been set to active. Connect the certificate provider here later.');
    }

    /**
     * @return array<string, mixed>
     */
    private function domainPayload(string $domain, bool $primary): array
    {
        $domain = $this->normalizeDomain($domain);

        $verificationToken = Str::random(40);

        return [
            'domain' => $domain,
            'is_primary' => $primary,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
            'verification_token' => $verificationToken,
            'verification_payload' => [
                'type' => 'CNAME',
                'name' => $domain,
                'value' => 'cname.jobboardsoftware.co',
                'txt_name' => '_jobboardsoftware.'.$domain,
                'txt_value' => $verificationToken,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function subdomainPayload(string $slug): array
    {
        return [
            'domain' => $slug.'.'.self::PLATFORM_DOMAIN,
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
            'verified_at' => now(),
            'ssl_issued_at' => now(),
        ];
    }

    private function normalizeDomain(string $domain): string
    {
        return Str::of($domain)
            ->lower()
            ->replace(['https://', 'http://'], '')
            ->before('/')
            ->trim()
            ->toString();
    }
}

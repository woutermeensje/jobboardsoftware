<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantEnvironmentController extends Controller
{
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
        if ($request->filled('domain')) {
            $request->merge(['domain' => $this->normalizeDomain($request->string('domain')->toString())]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('tenants', 'slug')],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('domains', 'domain')],
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
            'onboarding_step' => empty($validated['domain']) ? 'domain' : 'jobs',
            'trial_ends_at' => now()->addDays(14),
            'settings' => [
                'brand_name' => $validated['name'],
                'accent_color' => '#2f5f80',
                'intro' => 'Bekijk actuele vacatures en solliciteer direct.',
            ],
        ]);

        if (! empty($validated['domain'])) {
            $tenant->domains()->create($this->domainPayload($validated['domain'], true));
        }

        $request->user()->forceFill([
            'onboarding_step' => empty($validated['domain']) ? 'domain' : 'jobs',
        ])->save();

        return redirect()
            ->route('tenant.environments.index')
            ->with('status', 'Je jobboard omgeving is aangemaakt.');
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

        return redirect()
            ->route('tenant.environments.index')
            ->with('status', 'Domein toegevoegd. Voeg de CNAME toe bij je DNS-provider om verificatie af te ronden.');
    }

    public function checkDomain(Request $request, Tenant $tenant, Domain $domain): RedirectResponse
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);
        abort_unless($domain->tenant_id === $tenant->id, 404);

        $payload = $domain->verification_payload ?? [];
        $txtRecords = $this->dnsRecords($payload['txt_name'] ?? '', DNS_TXT);
        $cnameRecords = $this->dnsRecords($domain->domain, DNS_CNAME);

        $txtValue = $payload['txt_value'] ?? null;
        $cnameValue = $payload['value'] ?? null;

        $txtVerified = $txtValue && collect($txtRecords)->contains(fn (array $record) => str_contains((string) ($record['txt'] ?? ''), $txtValue));
        $cnameVerified = $cnameValue && collect($cnameRecords)->contains(fn (array $record) => str_contains(rtrim((string) ($record['target'] ?? ''), '.'), $cnameValue));

        if ($txtVerified || $cnameVerified) {
            $domain->forceFill([
                'status' => Domain::STATUS_VERIFIED,
                'verified_at' => now(),
            ])->save();

            return back()->with('status', 'Domein geverifieerd. SSL kan nu worden voorbereid.');
        }

        $domain->forceFill([
            'status' => Domain::STATUS_FAILED,
        ])->save();

        return back()->with('status', 'DNS records nog niet gevonden. Controleer de CNAME of TXT waarde en probeer opnieuw.');
    }

    public function issueSsl(Request $request, Tenant $tenant, Domain $domain): RedirectResponse
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);
        abort_unless($domain->tenant_id === $tenant->id, 404);
        abort_unless(in_array($domain->status, [Domain::STATUS_VERIFIED, Domain::STATUS_ACTIVE], true), 422);

        $domain->forceFill([
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
            'ssl_issued_at' => now(),
        ])->save();

        return back()->with('status', 'SSL status is actief gezet. Koppel hier later de certificaatprovider aan.');
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

    private function normalizeDomain(string $domain): string
    {
        return Str::of($domain)
            ->lower()
            ->replace(['https://', 'http://'], '')
            ->before('/')
            ->trim()
            ->toString();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function dnsRecords(string $host, int $type): array
    {
        if ($host === '') {
            return [];
        }

        $records = @dns_get_record($host, $type);

        return is_array($records) ? $records : [];
    }
}

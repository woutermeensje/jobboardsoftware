@extends('layouts.app')

@section('title', 'Domains | Client dashboard')
@section('meta_description', 'Manage connected domains for your job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedTenantId = old('tenant_id', $tenants->first()?->id);
  $domainDefaults = config('services.laravel_cloud.domain_defaults', []);
  $wwwRedirect = old('www_redirect', $domainDefaults['www_redirect'] ?? '');
  $cloudflareStrategy = old('cloudflare_strategy', $domainDefaults['cloudflare_strategy'] ?? \App\Models\Domain::CLOUDFLARE_NONE);
  $verificationMethod = old('verification_method', $domainDefaults['verification_method'] ?? \App\Models\Domain::VERIFICATION_REAL_TIME);
  $allowDowntime = old('allow_downtime', ($domainDefaults['allow_downtime'] ?? true) ? '1' : '0');
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Domain could not be connected.</strong>
          <ul class="dash-message-list">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      <div class="dash-form-layout">
        <main class="dash-form-layout__main">
          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Connect domain</h2>
                <p>Connect an apex domain, subdomain, or wildcard domain to your job board environment. Once Laravel Cloud verification succeeds, it replaces the environment's current primary domain.</p>
              </div>
            </div>

            @if($tenants->isEmpty())
              <div class="dash-empty">
                <h3>No environments yet</h3>
                <p>Create an environment before connecting a custom domain.</p>
                <div class="dash-actions">
                  <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
                </div>
              </div>
            @else
              <form class="domain-form" method="POST" action="{{ route('client.domains.store') }}">
                @csrf

                <div class="domain-form__grid">
                  <label class="domain-field">
                    <span>Environment</span>
                    <select name="tenant_id" required>
                      @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($selectedTenantId === $tenant->id)>
                          {{ $tenant->name }} ({{ $tenant->slug }})
                        </option>
                      @endforeach
                    </select>
                  </label>

                  <label class="domain-field">
                    <span>Domain name</span>
                    <input
                      type="text"
                      name="domain"
                      value="{{ old('domain') }}"
                      placeholder="example.com or careers.example.com"
                      autocomplete="off"
                      required
                    >
                  </label>

                  <label class="domain-field">
                    <span>WWW redirect</span>
                    <select name="www_redirect">
                      <option value="" @selected($wwwRedirect === '')>No www redirect</option>
                      <option value="{{ \App\Models\Domain::WWW_TO_ROOT }}" @selected($wwwRedirect === \App\Models\Domain::WWW_TO_ROOT)>www to root</option>
                      <option value="{{ \App\Models\Domain::ROOT_TO_WWW }}" @selected($wwwRedirect === \App\Models\Domain::ROOT_TO_WWW)>root to www</option>
                    </select>
                  </label>

                  <label class="domain-field">
                    <span>Verification path</span>
                    <select name="verification_method">
                      <option value="{{ \App\Models\Domain::VERIFICATION_REAL_TIME }}" @selected($verificationMethod === \App\Models\Domain::VERIFICATION_REAL_TIME)>Real-time</option>
                      <option value="{{ \App\Models\Domain::VERIFICATION_PRE_VERIFICATION }}" @selected($verificationMethod === \App\Models\Domain::VERIFICATION_PRE_VERIFICATION)>Pre-verification</option>
                    </select>
                  </label>

                  <label class="domain-field">
                    <span>Cloudflare setup</span>
                    <select name="cloudflare_strategy" required>
                      <option value="{{ \App\Models\Domain::CLOUDFLARE_NONE }}" @selected($cloudflareStrategy === \App\Models\Domain::CLOUDFLARE_NONE)>No Cloudflare</option>
                      <option value="{{ \App\Models\Domain::CLOUDFLARE_DNS }}" @selected($cloudflareStrategy === \App\Models\Domain::CLOUDFLARE_DNS)>Cloudflare DNS only</option>
                      <option value="{{ \App\Models\Domain::CLOUDFLARE_DNS_PROXY }}" @selected($cloudflareStrategy === \App\Models\Domain::CLOUDFLARE_DNS_PROXY)>Cloudflare proxied</option>
                    </select>
                  </label>

                  <label class="domain-field">
                    <span>Downtime preference</span>
                    <select name="allow_downtime">
                      <option value="1" @selected($allowDowntime === '1')>Flexible</option>
                      <option value="0" @selected($allowDowntime === '0')>Uninterrupted</option>
                    </select>
                  </label>
                </div>

                <label class="domain-switch">
                  <input type="checkbox" name="wildcard_enabled" value="1" @checked(old('wildcard_enabled', $domainDefaults['wildcard_enabled'] ?? false))>
                  <span>Enable wildcard subdomains</span>
                </label>

                <div class="dash-actions dash-actions--spaced">
                  <button class="dash-btn dash-btn--primary" type="submit">
                    <i class="ph ph-link" aria-hidden="true"></i>
                    Connect domain
                  </button>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>DNS setup</h2>
            <p>After connecting a domain, Laravel Cloud returns the DNS records needed for ownership, SSL, and origin verification.</p>
            <ul>
              <li>Use an apex domain such as example.com or a subdomain such as careers.example.com.</li>
              <li>Wildcard domains require pre-verification unless the hostname is already Cloudflare proxied.</li>
              <li>SSL is issued by Laravel Cloud after the required DNS records resolve.</li>
            </ul>
          </section>
        </aside>
      </div>

      <section class="dash-panel dash-panel--list">
        <div class="dash-panel__head">
          <div>
            <h2>Connected domains</h2>
            <p>Pending domains become available after DNS verification and SSL activation.</p>
          </div>
        </div>

        <div class="dash-panel__body">
          @if($domains->isEmpty())
            <div class="dash-empty">
              <h3>No domains connected</h3>
              <p>Your connected domains will appear here.</p>
            </div>
          @else
            <table class="dash-table domain-table">
              <thead>
                <tr>
                  <th>Domain</th>
                  <th>Environment</th>
                  <th>Status</th>
                  <th>DNS records</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($domains as $domain)
                  @php
                    $payload = $domain->verification_payload ?? [];
                    $cloudRecords = $domain->cloudDnsRecords();
                  @endphp
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $domain->domain }}</span>
                      <span class="dash-cell-meta">
                        {{ $domain->is_primary ? 'Live domain' : 'Pending — will replace the live domain once verified' }}
                      </span>
                    </td>
                    <td>
                      <span class="dash-cell-title">{{ $domain->tenant?->name ?? $domain->tenant_id }}</span>
                      <span class="dash-cell-meta">{{ $domain->tenant_id }}</span>
                    </td>
                    <td>
                      <span class="dash-status">{{ ucfirst($domain->status) }}</span>
                      <span class="dash-cell-meta">SSL: {{ ucfirst($domain->ssl_status) }}</span>
                      @if($domain->usesLaravelCloud())
                        <span class="dash-cell-meta">
                          Cloud: hostname {{ ucfirst($domain->cloud_hostname_status ?? 'pending') }},
                          origin {{ ucfirst($domain->cloud_origin_status ?? 'pending') }}
                        </span>
                        @if($domain->cloud_action_required)
                          <span class="dash-cell-meta">Action: {{ str($domain->cloud_action_required)->replace('_', ' ')->headline() }}</span>
                        @endif
                      @endif
                    </td>
                    <td>
                      <div class="domain-records">
                        @forelse($cloudRecords as $record)
                          <div>
                            <span>{{ $record['type'] }}</span>
                            <code>{{ $record['name'] }}</code>
                            <code>{{ $record['value'] }}</code>
                          </div>
                        @empty
                          <div>
                            <span>CNAME</span>
                            <code>{{ $payload['host'] ?? $domain->domain }}</code>
                            <code>{{ $payload['value'] ?? $dnsTarget }}</code>
                          </div>
                          @if(! empty($payload['txt_name']) && ! empty($payload['txt_value']))
                            <div>
                              <span>TXT</span>
                              <code>{{ $payload['txt_name'] }}</code>
                              <code>{{ $payload['txt_value'] }}</code>
                            </div>
                          @endif
                        @endforelse
                      </div>
                    </td>
                    <td>
                      <div class="domain-actions">
                        @if($domain->isReadyForTraffic())
                          <span class="dash-status dash-status--muted">Ready</span>
                        @else
                          <form method="POST" action="{{ route('client.domains.verify', $domain) }}">
                            @csrf
                            <button class="dash-btn dash-btn--ghost btn-sm" type="submit">
                              <i class="ph ph-arrows-clockwise" aria-hidden="true"></i>
                              Check DNS
                            </button>
                          </form>
                        @endif

                        <form method="POST" action="{{ route('client.domains.destroy', $domain) }}" onsubmit="return confirm('Remove {{ addslashes($domain->domain) }} from this environment?');">
                          @csrf
                          @method('DELETE')
                          <button class="dash-btn dash-btn--ghost btn-sm" type="submit">
                            <i class="ph ph-trash" aria-hidden="true"></i>
                            Remove
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </section>
@endsection

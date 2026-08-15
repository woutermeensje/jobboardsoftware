@extends('layouts.app')

@section('title', 'Domains | Client dashboard')
@section('meta_description', 'Manage connected domains for your job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedTenantId = old('tenant_id', $tenants->first()?->id);
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

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>Connect domain</h2>
            <p>Add a custom domain to one of your job board environments.</p>
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
                  placeholder="careers.example.com"
                  autocomplete="off"
                  required
                >
              </label>
            </div>

            <label class="domain-switch">
              <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary'))>
              Primary domain for this environment
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

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>Connected domains</h2>
            <p>Pending domains become available after DNS verification and SSL activation.</p>
          </div>
        </div>

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
                @endphp
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $domain->domain }}</span>
                    <span class="dash-cell-meta">{{ $domain->is_primary ? 'Primary' : 'Additional' }}</span>
                  </td>
                  <td>
                    <span class="dash-cell-title">{{ $domain->tenant?->name ?? $domain->tenant_id }}</span>
                    <span class="dash-cell-meta">{{ $domain->tenant_id }}</span>
                  </td>
                  <td>
                    <span class="dash-status">{{ ucfirst($domain->status) }}</span>
                    <span class="dash-cell-meta">SSL: {{ ucfirst($domain->ssl_status) }}</span>
                  </td>
                  <td>
                    <div class="domain-records">
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
                    </div>
                  </td>
                  <td>
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
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </section>
@endsection

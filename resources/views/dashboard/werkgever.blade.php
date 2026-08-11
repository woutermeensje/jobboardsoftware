@extends('layouts.app')

@section('title', 'Job board management | JobBoardSoftware')
@section('meta_description', 'SaaS dashboard for job board licenses, tenant environments, domains and management settings.')

@php
  $tenantCollection = $tenants ?? collect();
  $domainCount = $tenantCollection->sum(fn ($tenant) => $tenant->domains->count());
  $activeTenants = $tenantCollection->where('status', \App\Models\Tenant::STATUS_ACTIVE)->count();
  $trialTenants = $tenantCollection->where('status', \App\Models\Tenant::STATUS_TRIAL)->count();

  $stats = [
    ['label' => 'Jobboards', 'value' => (string) $tenantCollection->count()],
    ['label' => 'Domains', 'value' => (string) $domainCount],
    ['label' => 'Active licenses', 'value' => (string) $activeTenants],
    ['label' => 'Trials', 'value' => (string) $trialTenants],
  ];

  $steps = [
    ['label' => 'SaaS account created', 'done' => true],
    ['label' => 'Start job board environment', 'done' => $tenantCollection->isNotEmpty()],
    ['label' => 'Connect custom domain', 'done' => $domainCount > 0],
    ['label' => 'Activate license', 'done' => $activeTenants > 0],
  ];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation')

    <div class="dash-content">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">SaaS management portal</p>
        <h1 class="dash-title">Welcome back, {{ $user->name }}</h1>
        <p class="dash-subtitle">Manage your job board software, license, tenant environments and connected domains.</p>
      </div>
      <aside class="dash-user" aria-label="Signed-in user">
        <strong>{{ $user->company_name ?: 'Jobboard account' }}</strong>
        <span>{{ $user->email }}</span>
        <span>Role: SaaS user</span>
      </aside>
    </header>

    <div class="dash-stats">
      @foreach($stats as $stat)
        <article class="dash-stat">
          <span>{{ $stat['label'] }}</span>
          <strong>{{ $stat['value'] }}</strong>
        </article>
      @endforeach
    </div>

    <div class="dash-layout">
      <main class="dash-main">
        <section class="dash-panel" aria-labelledby="owner-boards-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="owner-boards-title">My job boards</h2>
              <p>Environments that will run on your customer or brand domain.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">
              <i class="ph ph-plus"></i>
              Manage environment
            </a>
          </div>

          @if($tenantCollection->isEmpty())
            <div class="dash-empty">
              <h3>No job board environment yet</h3>
              <p>Create your first environment, connect a domain and publish the job frontend there.</p>
              <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">Create first environment</a>
            </div>
          @else
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Jobboard</th>
                  <th>Plan</th>
                  <th>Status</th>
                  <th>Domains</th>
                </tr>
              </thead>
              <tbody>
                @foreach($tenantCollection as $tenant)
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $tenant->name }}</span>
                      <span class="dash-cell-meta">{{ $tenant->slug }}</span>
                    </td>
                    <td>{{ ucfirst($tenant->plan) }}</td>
                    <td>
                      <span class="dash-status {{ $tenant->isActive() ? '' : 'dash-status--accent' }}">{{ ucfirst($tenant->status) }}</span>
                    </td>
                    <td>{{ $tenant->domains->count() }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </section>

        <section class="dash-panel" aria-labelledby="owner-flow-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="owner-flow-title">Implementation progress</h2>
              <p>The base flow for a customer buying and publishing job board software.</p>
            </div>
          </div>

          <ul class="dash-checklist dash-checklist--large">
            @foreach($steps as $step)
              <li>
                <i class="ph {{ $step['done'] ? 'ph-check-circle' : 'ph-circle' }}"></i>
                {{ $step['label'] }}
              </li>
            @endforeach
          </ul>
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card">
          <h2>Quick actions</h2>
          <p>Jump straight to the most important parts of the SaaS portal.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('onboarding.index') }}">Onboarding</a>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">Manage environments</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('billing.index') }}">View license</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('pages.contact') }}">Ask support</a>
          </div>
        </section>

        <section class="dash-card">
          <h2>Connect domain</h2>
          <p>The job board frontend only becomes visible on the domain connected to a tenant.</p>
          <ul class="dash-list">
            <li>
              <div>
                <strong>CNAME target</strong>
                <span>cname.jobboardsoftware.co</span>
              </div>
              <span>DNS</span>
            </li>
            <li>
              <div>
                <strong>SSL status</strong>
                <span>Prepared per domain</span>
              </div>
              <span>Auto</span>
            </li>
          </ul>
        </section>

        <form method="POST" action="{{ route('logout') }}" class="dash-card">
          @csrf
          <h2>Session</h2>
          <p>You are signed in to the central SaaS management portal.</p>
          <div class="dash-actions dash-actions--spaced">
            <button class="dash-btn dash-btn--ghost" type="submit">Log out</button>
          </div>
        </form>
      </aside>
    </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
@endpush

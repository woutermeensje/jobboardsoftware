@extends('layouts.app')

@section('title', 'Onboarding | JobBoardSoftware')
@section('meta_description', 'Complete onboarding for your SaaS job board environment.')

@php
  $stepLabels = [
    'plan' => 'Choose package',
    'environment' => 'Job board environment',
    'domain' => 'Connect domain',
    'jobs' => 'First job',
    'billing' => 'License status',
  ];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation', ['activeTenant' => $tenant])

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Onboarding</p>
          <h1 class="dash-title">Launch your job board</h1>
          <p class="dash-subtitle">Complete the full start flow: choose a package, create a job board, connect a domain and publish your first job.</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $user->company_name ?: $user->name }}</strong>
          <span>Package: {{ $user->billingPlan?->name ?? 'Not selected yet' }}</span>
          <span>Status: {{ ucfirst($user->billing_status ?? 'trial') }}</span>
        </aside>
      </header>

      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <div class="dash-layout">
        <main class="dash-main">
          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Progress</h2>
                <p>The central checklist for billing, tenant, domain and jobs.</p>
              </div>
            </div>

            <ul class="dash-checklist dash-checklist--large">
              @foreach($stepLabels as $key => $label)
                <li>
                  <i class="ph {{ $steps[$key] ? 'ph-check-circle' : 'ph-circle' }}"></i>
                  {{ $label }}
                </li>
              @endforeach
            </ul>
          </section>

          <section class="dash-panel" id="package">
            <div class="dash-panel__head">
              <div>
                <h2>1. Choose package</h2>
                <p>Select the license this customer starts with.</p>
              </div>
            </div>
            <div class="onboarding-plan-grid">
              @foreach($plans as $plan)
                <form class="onboarding-plan {{ $user->billing_plan_id === $plan->id ? 'is-selected' : '' }}" method="POST" action="{{ route('billing.plan.select') }}">
                  @csrf
                  <input type="hidden" name="plan_key" value="{{ $plan->key }}">
                  <h3>{{ $plan->name }}</h3>
                  <p>{{ $plan->description }}</p>
                  <strong>{{ $plan->formattedMonthlyPrice() }}</strong>
                  <button class="dash-btn {{ $user->billing_plan_id === $plan->id ? 'dash-btn--ghost' : 'dash-btn--primary' }}" type="submit">
                    {{ $user->billing_plan_id === $plan->id ? 'Selected package' : 'Choose package' }}
                  </button>
                </form>
              @endforeach
            </div>
          </section>

          <section class="dash-panel" id="environment">
            <div class="dash-panel__head">
              <div>
                <h2>2. Job board environment</h2>
                <p>Create the tenant that will run the job frontend.</p>
              </div>
            </div>

            @if($tenant)
              <div class="onboarding-complete-row">
                <div>
                  <strong>{{ $tenant->name }}</strong>
                  <span>{{ $tenant->slug }} - {{ ucfirst($tenant->plan) }} - {{ ucfirst($tenant->status) }}</span>
                </div>
                <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.environments.index') }}">Manage</a>
              </div>
            @else
              <form class="form onboarding-form" method="POST" action="{{ route('tenant.environments.store') }}">
                @csrf
                <div class="form-grid form-grid--two">
                  <div class="form-field">
                    <label class="form-label" for="name">Job board name</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Acme Careers" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-field">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control" id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="acme-careers" required>
                    @error('slug')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                </div>
                <div class="form-field">
                  <label class="form-label" for="domain">Domain or subdomain</label>
                  <input class="form-control" id="domain" name="domain" type="text" value="{{ old('domain') }}" placeholder="jobs.example.com">
                  <p class="form-help">You can connect this later as well.</p>
                  @error('domain')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">Create environment</button>
                </div>
              </form>
            @endif
          </section>

          <section class="dash-panel" id="domain">
            <div class="dash-panel__head">
              <div>
                <h2>3. Connect domain</h2>
                <p>Point the customer domain to your SaaS target.</p>
              </div>
            </div>

            @if(! $tenant)
              <div class="onboarding-disabled">Create a job board environment first.</div>
            @else
              <div class="onboarding-domain-list">
                @forelse($tenant->domains as $domain)
                  <div class="onboarding-complete-row">
                    <div>
                      <strong>{{ $domain->domain }}</strong>
                      <span>DNS: {{ ucfirst($domain->status) }} - SSL: {{ ucfirst($domain->ssl_status) }}</span>
                    </div>
                    <form method="POST" action="{{ route('tenant.environments.domains.check', [$tenant, $domain]) }}">
                      @csrf
                      <button class="dash-btn dash-btn--ghost" type="submit">DNS check</button>
                    </form>
                  </div>
                @empty
                  <p class="onboarding-muted">No domain connected yet.</p>
                @endforelse
              </div>

              <form class="form onboarding-form" method="POST" action="{{ route('tenant.environments.domains.store', $tenant) }}">
                @csrf
                <div class="form-field">
                  <label class="form-label" for="extra-domain">Add domain</label>
                  <input class="form-control" id="extra-domain" name="domain" type="text" placeholder="jobs.example.com" required>
                  @error('domain')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">Connect domain</button>
                </div>
              </form>
            @endif
          </section>

          <section class="dash-panel" id="job">
            <div class="dash-panel__head">
              <div>
                <h2>4. First job</h2>
                <p>Publish the first job directly on the tenant frontend.</p>
              </div>
            </div>

            @if(! $tenant)
              <div class="onboarding-disabled">Create a job board environment first.</div>
            @elseif($tenant->jobs->isNotEmpty())
              <div class="onboarding-complete-row">
                <div>
                  <strong>{{ $tenant->jobs->first()->title }}</strong>
                  <span>{{ $tenant->jobs->count() }} job(s) created.</span>
                </div>
                <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.jobs.index', $tenant) }}">Manage jobs</a>
              </div>
            @else
              <form class="form onboarding-form" method="POST" action="{{ route('tenant.jobs.store', $tenant) }}">
                @csrf
                <input type="hidden" name="status" value="{{ \App\Models\TenantJob::STATUS_PUBLISHED }}">
                <div class="form-grid form-grid--two">
                  <div class="form-field">
                    <label class="form-label" for="job-title">Job title</label>
                    <input class="form-control" id="job-title" name="title" type="text" placeholder="Laravel Developer" required>
                  </div>
                  <div class="form-field">
                    <label class="form-label" for="job-location">Location</label>
                    <input class="form-control" id="job-location" name="location" type="text" placeholder="Amsterdam">
                  </div>
                </div>
                <div class="form-grid form-grid--two">
                  <div class="form-field">
                    <label class="form-label" for="job-department">Department</label>
                    <input class="form-control" id="job-department" name="department" type="text" placeholder="Development">
                  </div>
                  <div class="form-field">
                    <label class="form-label" for="job-type">Employment type</label>
                    <input class="form-control" id="job-type" name="employment_type" type="text" placeholder="Fulltime">
                  </div>
                </div>
                <div class="form-field">
                  <label class="form-label" for="job-intro">Short intro</label>
                  <textarea class="form-control" id="job-intro" name="intro" rows="3" placeholder="Short summary of the role."></textarea>
                </div>
                <div class="form-field">
                  <label class="form-label" for="job-description">Job description</label>
                  <textarea class="form-control" id="job-description" name="description" rows="6" placeholder="Describe the role, responsibilities and what candidates can expect."></textarea>
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">Publish job</button>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-sidebar">
          <section class="dash-card">
            <h2>What happens here?</h2>
            <p>This wizard prepares the central SaaS user to manage a job board on a connected domain.</p>
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
                  <strong>Checkout</strong>
                  <span>Cashier uses Stripe once price IDs are filled in.</span>
                </div>
                <span>Stripe</span>
              </li>
            </ul>
          </section>
        </aside>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
  <style>
    .dash-card--success {
      border-color: var(--color-primary-muted);
      background: var(--color-primary-soft);
      color: var(--color-primary-strong);
    }

    .onboarding-plan-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
      padding: 18px;
    }

    .onboarding-plan {
      display: grid;
      gap: 12px;
      align-content: space-between;
      min-height: 250px;
      margin: 0;
      padding: 18px;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-default);
      background: #ffffff;
    }

    .onboarding-plan.is-selected {
      border-color: var(--color-primary-strong);
      background: var(--color-primary-soft);
    }

    .onboarding-plan h3,
    .onboarding-plan p {
      margin: 0;
    }

    .onboarding-plan strong {
      color: var(--color-primary-strong);
      font-family: var(--font-ui);
      font-size: 20px;
    }

    .onboarding-form {
      display: grid;
      gap: 16px;
      padding: 20px;
    }

    .onboarding-domain-list {
      display: grid;
      gap: 10px;
      padding: 20px 20px 0;
    }

    .onboarding-complete-row,
    .onboarding-disabled,
    .onboarding-muted {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding: 18px 20px;
      border-top: 1px solid var(--color-border);
      background: #ffffff;
    }

    .onboarding-complete-row:first-child {
      border-top: 0;
    }

    .onboarding-complete-row strong,
    .onboarding-complete-row span {
      display: block;
    }

    .onboarding-complete-row span,
    .onboarding-disabled,
    .onboarding-muted {
      color: var(--color-text-muted);
      font-size: 14px;
    }

    @media (max-width: 980px) {
      .onboarding-plan-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 680px) {
      .onboarding-complete-row {
        display: grid;
      }
    }
  </style>
@endpush

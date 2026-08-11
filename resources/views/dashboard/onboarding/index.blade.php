@extends('layouts.app')

@section('title', 'Onboarding | JobBoardSoftware')
@section('meta_description', 'Doorloop de onboarding voor je SaaS jobboard omgeving.')

@php
  $stepLabels = [
    'plan' => 'Pakket kiezen',
    'environment' => 'Jobboard omgeving',
    'domain' => 'Domein koppelen',
    'jobs' => 'Eerste vacature',
    'billing' => 'Licentie status',
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
          <h1 class="dash-title">Zet je jobboard live</h1>
          <p class="dash-subtitle">Doorloop de volledige startflow: pakket kiezen, jobboard aanmaken, domein koppelen en je eerste vacature publiceren.</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $user->company_name ?: $user->name }}</strong>
          <span>Pakket: {{ $user->billingPlan?->name ?? 'Nog niet gekozen' }}</span>
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
                <h2>Voortgang</h2>
                <p>De centrale checklist voor billing, tenant, domein en vacatures.</p>
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

          <section class="dash-panel" id="pakket">
            <div class="dash-panel__head">
              <div>
                <h2>1. Pakket kiezen</h2>
                <p>Selecteer de licentie waarmee deze klant start.</p>
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
                    {{ $user->billing_plan_id === $plan->id ? 'Gekozen pakket' : 'Kies pakket' }}
                  </button>
                </form>
              @endforeach
            </div>
          </section>

          <section class="dash-panel" id="omgeving">
            <div class="dash-panel__head">
              <div>
                <h2>2. Jobboard omgeving</h2>
                <p>Maak de tenant aan waar de vacaturefrontend straks op draait.</p>
              </div>
            </div>

            @if($tenant)
              <div class="onboarding-complete-row">
                <div>
                  <strong>{{ $tenant->name }}</strong>
                  <span>{{ $tenant->slug }} - {{ ucfirst($tenant->plan) }} - {{ ucfirst($tenant->status) }}</span>
                </div>
                <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.environments.index') }}">Beheren</a>
              </div>
            @else
              <form class="form onboarding-form" method="POST" action="{{ route('tenant.environments.store') }}">
                @csrf
                <div class="form-grid form-grid--two">
                  <div class="form-field">
                    <label class="form-label" for="name">Naam jobboard</label>
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
                  <label class="form-label" for="domain">Domein of subdomein</label>
                  <input class="form-control" id="domain" name="domain" type="text" value="{{ old('domain') }}" placeholder="vacatures.voorbeeld.nl">
                  <p class="form-help">Je kunt dit ook later koppelen.</p>
                  @error('domain')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">Omgeving aanmaken</button>
                </div>
              </form>
            @endif
          </section>

          <section class="dash-panel" id="domein">
            <div class="dash-panel__head">
              <div>
                <h2>3. Domein koppelen</h2>
                <p>Laat het klantdomein naar jullie SaaS target wijzen.</p>
              </div>
            </div>

            @if(! $tenant)
              <div class="onboarding-disabled">Maak eerst een jobboard omgeving aan.</div>
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
                  <p class="onboarding-muted">Nog geen domein gekoppeld.</p>
                @endforelse
              </div>

              <form class="form onboarding-form" method="POST" action="{{ route('tenant.environments.domains.store', $tenant) }}">
                @csrf
                <div class="form-field">
                  <label class="form-label" for="extra-domain">Domein toevoegen</label>
                  <input class="form-control" id="extra-domain" name="domain" type="text" placeholder="vacatures.voorbeeld.nl" required>
                  @error('domain')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">Domein koppelen</button>
                </div>
              </form>
            @endif
          </section>

          <section class="dash-panel" id="vacature">
            <div class="dash-panel__head">
              <div>
                <h2>4. Eerste vacature</h2>
                <p>Publiceer direct de eerste vacature op de tenant frontend.</p>
              </div>
            </div>

            @if(! $tenant)
              <div class="onboarding-disabled">Maak eerst een jobboard omgeving aan.</div>
            @elseif($tenant->jobs->isNotEmpty())
              <div class="onboarding-complete-row">
                <div>
                  <strong>{{ $tenant->jobs->first()->title }}</strong>
                  <span>{{ $tenant->jobs->count() }} vacature(s) aangemaakt.</span>
                </div>
                <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.jobs.index', $tenant) }}">Vacatures beheren</a>
              </div>
            @else
              <form class="form onboarding-form" method="POST" action="{{ route('tenant.jobs.store', $tenant) }}">
                @csrf
                <input type="hidden" name="status" value="{{ \App\Models\TenantJob::STATUS_PUBLISHED }}">
                <div class="form-grid form-grid--two">
                  <div class="form-field">
                    <label class="form-label" for="job-title">Vacaturetitel</label>
                    <input class="form-control" id="job-title" name="title" type="text" placeholder="Laravel Developer" required>
                  </div>
                  <div class="form-field">
                    <label class="form-label" for="job-location">Locatie</label>
                    <input class="form-control" id="job-location" name="location" type="text" placeholder="Amsterdam">
                  </div>
                </div>
                <div class="form-grid form-grid--two">
                  <div class="form-field">
                    <label class="form-label" for="job-department">Afdeling</label>
                    <input class="form-control" id="job-department" name="department" type="text" placeholder="Development">
                  </div>
                  <div class="form-field">
                    <label class="form-label" for="job-type">Dienstverband</label>
                    <input class="form-control" id="job-type" name="employment_type" type="text" placeholder="Fulltime">
                  </div>
                </div>
                <div class="form-field">
                  <label class="form-label" for="job-intro">Korte intro</label>
                  <textarea class="form-control" id="job-intro" name="intro" rows="3" placeholder="Korte samenvatting van de functie."></textarea>
                </div>
                <div class="form-field">
                  <label class="form-label" for="job-description">Vacaturetekst</label>
                  <textarea class="form-control" id="job-description" name="description" rows="6" placeholder="Beschrijf de rol, werkzaamheden en wat kandidaten kunnen verwachten."></textarea>
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">Vacature publiceren</button>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-sidebar">
          <section class="dash-card">
            <h2>Wat gebeurt hier?</h2>
            <p>Deze wizard maakt de centrale SaaS gebruiker klaar om een eigen jobboard op een gekoppeld domein te beheren.</p>
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
                  <span>Cashier gebruikt Stripe zodra price IDs zijn gevuld.</span>
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
      border-radius: 8px;
      background: #fbfdff;
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
      background: #fbfdff;
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

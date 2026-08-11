@extends('layouts.app')

@section('title', 'Jobboard omgeving beheren | JobBoardSoftware')
@section('meta_description', 'Beheer tenant jobboard omgevingen, domeinen en DNS-koppelingen.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation')

    <div class="dash-content">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">SaaS omgeving</p>
        <h1 class="dash-title">Jobboard omgeving beheren</h1>
        <p class="dash-subtitle">Maak een tenant omgeving aan, koppel je domeinnaam en beheer de basis van je eigen vacaturefrontend.</p>
      </div>
      <aside class="dash-user" aria-label="Ingelogde gebruiker">
        <strong>{{ $user->company_name ?: $user->name }}</strong>
        <span>{{ $user->email }}</span>
        <span>Licentiebeheer</span>
      </aside>
    </header>

    @if(session('status'))
      <section class="dash-card dash-card--success">
        <strong>{{ session('status') }}</strong>
      </section>
    @endif

    <div class="dash-layout">
      <main class="dash-main">
        <section class="dash-panel" aria-labelledby="tenant-list-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="tenant-list-title">Mijn omgevingen</h2>
              <p>Elke omgeving kan een of meerdere domeinen krijgen.</p>
            </div>
          </div>

          @forelse($tenants as $tenant)
            <article class="tenant-environment">
              <div class="tenant-environment__head">
                <div>
                  <h3>{{ $tenant->name }}</h3>
                  <p>{{ $tenant->slug }} - {{ ucfirst($tenant->plan) }} - {{ ucfirst($tenant->status) }}</p>
                </div>
                <span class="dash-status {{ $tenant->isActive() ? '' : 'dash-status--accent' }}">{{ ucfirst($tenant->status) }}</span>
              </div>

              <div class="dash-actions">
                <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs.index', $tenant) }}">Vacatures beheren</a>
                <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.applications.index', $tenant) }}">Sollicitaties</a>
                <a class="dash-btn dash-btn--ghost" href="{{ route('onboarding.index') }}">Onboarding</a>
              </div>

              <div class="tenant-domain-list">
                @forelse($tenant->domains as $domain)
                  <div class="tenant-domain">
                    <div>
                      <strong>{{ $domain->domain }}</strong>
                      <span>{{ $domain->is_primary ? 'Primair domein' : 'Extra domein' }} - DNS: {{ ucfirst($domain->status) }} - SSL: {{ ucfirst($domain->ssl_status) }}</span>
                    </div>
                    <div class="tenant-domain__ops">
                      <code>CNAME {{ $domain->domain }} -> cname.jobboardsoftware.co</code>
                      <form method="POST" action="{{ route('tenant.environments.domains.check', [$tenant, $domain]) }}">
                        @csrf
                        <button class="dash-btn dash-btn--ghost" type="submit">DNS check</button>
                      </form>
                      <form method="POST" action="{{ route('tenant.environments.domains.ssl', [$tenant, $domain]) }}">
                        @csrf
                        <button class="dash-btn dash-btn--ghost" type="submit" @disabled(! in_array($domain->status, [\App\Models\Domain::STATUS_VERIFIED, \App\Models\Domain::STATUS_ACTIVE], true))>SSL actief</button>
                      </form>
                    </div>
                  </div>
                @empty
                  <p class="dash-cell-meta">Nog geen domein gekoppeld.</p>
                @endforelse
              </div>

              <form class="form tenant-domain-form" method="POST" action="{{ route('tenant.environments.domains.store', $tenant) }}">
                @csrf
                <div class="form-field">
                  <label class="form-label" for="domain-{{ $tenant->id }}">Extra domein koppelen</label>
                  <input class="form-control" id="domain-{{ $tenant->id }}" name="domain" type="text" placeholder="vacatures.voorbeeld.nl">
                  @error('domain')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
                <div class="form-actions">
                  <button class="dash-btn dash-btn--ghost" type="submit">Domein toevoegen</button>
                </div>
              </form>
            </article>
          @empty
            <div class="tenant-environment tenant-environment--empty">
              <h3>Nog geen omgeving</h3>
              <p>Maak je eerste jobboard omgeving aan en koppel direct een domein of subdomein.</p>
            </div>
          @endforelse
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card">
          <h2>Nieuwe omgeving</h2>
          <p>Start met het gekozen pakket. Billing loopt lokaal in trialmodus totdat Stripe price IDs zijn ingesteld.</p>
          <form class="form tenant-create-form" method="POST" action="{{ route('tenant.environments.store') }}">
            @csrf
            <div class="form-field">
              <label class="form-label" for="name">Naam jobboard</label>
              <input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Acme Careers" required>
              @error('name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-field">
              <label class="form-label" for="slug">Slug</label>
              <input class="form-control" id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="acme-careers" required>
              @error('slug')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-field">
              <label class="form-label" for="domain">Domein</label>
              <input class="form-control" id="domain" name="domain" type="text" value="{{ old('domain') }}" placeholder="vacatures.voorbeeld.nl">
              <p class="form-help">Laat leeg als je later een domein wilt koppelen.</p>
              @error('domain')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-actions">
              <button class="dash-btn dash-btn--primary" type="submit">Omgeving aanmaken</button>
            </div>
          </form>
        </section>

        <section class="dash-card">
          <h2>DNS instructie</h2>
          <p>Laat klanten hun domein of subdomein als CNAME naar jullie SaaS target wijzen.</p>
          <ul class="dash-list">
            <li>
              <div>
                <strong>Type</strong>
                <span>CNAME</span>
              </div>
              <span>DNS</span>
            </li>
            <li>
              <div>
                <strong>Waarde</strong>
                <span>cname.jobboardsoftware.co</span>
              </div>
              <span>Target</span>
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

    .tenant-environment {
      display: grid;
      gap: 16px;
      padding: 20px;
      border-bottom: 1px solid var(--color-border);
    }

    .tenant-environment:last-child {
      border-bottom: 0;
    }

    .tenant-environment__head,
    .tenant-domain {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 18px;
    }

    .tenant-environment h3,
    .tenant-environment p {
      margin: 0;
    }

    .tenant-environment h3 {
      font-size: 20px;
      font-weight: 800;
    }

    .tenant-environment p {
      color: var(--color-text-muted);
      font-size: 14px;
    }

    .tenant-domain-list {
      display: grid;
      gap: 10px;
    }

    .tenant-domain {
      padding: 12px;
      border: 1px solid var(--color-border);
      border-radius: 8px;
      background: #fbfdff;
    }

    .tenant-domain strong,
    .tenant-domain span {
      display: block;
    }

    .tenant-domain span {
      color: var(--color-text-muted);
      font-size: 13px;
    }

    .tenant-domain code {
      align-self: center;
      color: var(--color-primary-strong);
      font-size: 12px;
      white-space: nowrap;
    }

    .tenant-domain__ops {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 8px;
    }

    .tenant-domain__ops form {
      margin: 0;
    }

    .tenant-domain-form,
    .tenant-create-form {
      margin-top: 2px;
    }

    @media (max-width: 760px) {
      .tenant-environment__head,
      .tenant-domain {
        display: grid;
      }

      .tenant-domain code {
        white-space: normal;
      }
    }
  </style>
@endpush

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
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">Onboarding</p>
        <h1 class="dash-title">Zet je jobboard live</h1>
        <p class="dash-subtitle">Doorloop deze stappen om van account naar werkende jobboard frontend op je eigen domein te gaan.</p>
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

        <section class="dash-panel">
          <div class="dash-panel__head">
            <div>
              <h2>Volgende actie</h2>
              <p>Kies hieronder wat nog ontbreekt.</p>
            </div>
          </div>
          <div class="dash-onboarding-actions">
            <a class="dash-btn dash-btn--primary" href="{{ route('billing.index') }}">Pakket kiezen</a>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">Omgeving beheren</a>
            @if($tenant)
              <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs.create', $tenant) }}">Vacature plaatsen</a>
              <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.applications.index', $tenant) }}">Sollicitaties bekijken</a>
            @endif
          </div>
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card">
          <h2>Huidige omgeving</h2>
          @if($tenant)
            <p>{{ $tenant->name }} gebruikt plan {{ ucfirst($tenant->plan) }}.</p>
            <ul class="dash-list">
              <li>
                <div>
                  <strong>Domeinen</strong>
                  <span>{{ $tenant->domains->count() }} gekoppeld</span>
                </div>
                <span>DNS</span>
              </li>
              <li>
                <div>
                  <strong>Vacatures</strong>
                  <span>{{ $tenant->jobs->count() }} aangemaakt</span>
                </div>
                <span>Jobs</span>
              </li>
            </ul>
          @else
            <p>Nog geen jobboard omgeving aangemaakt.</p>
          @endif
        </section>
      </aside>
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

    .dash-onboarding-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      padding: 20px;
      border-top: 1px solid var(--color-border);
    }
  </style>
@endpush

@extends('layouts.app')

@section('title', 'Jobboard beheer | JobBoardSoftware')
@section('meta_description', 'SaaS dashboard voor jobboard licenties, tenant omgevingen, domeinen en beheerinstellingen.')

@php
  $tenantCollection = $tenants ?? collect();
  $domainCount = $tenantCollection->sum(fn ($tenant) => $tenant->domains->count());
  $activeTenants = $tenantCollection->where('status', \App\Models\Tenant::STATUS_ACTIVE)->count();
  $trialTenants = $tenantCollection->where('status', \App\Models\Tenant::STATUS_TRIAL)->count();

  $stats = [
    ['label' => 'Jobboards', 'value' => (string) $tenantCollection->count()],
    ['label' => 'Domeinen', 'value' => (string) $domainCount],
    ['label' => 'Actieve licenties', 'value' => (string) $activeTenants],
    ['label' => 'Trials', 'value' => (string) $trialTenants],
  ];

  $steps = [
    ['label' => 'SaaS account aangemaakt', 'done' => true],
    ['label' => 'Jobboard omgeving starten', 'done' => $tenantCollection->isNotEmpty()],
    ['label' => 'Eigen domein koppelen', 'done' => $domainCount > 0],
    ['label' => 'Licentie activeren', 'done' => $activeTenants > 0],
  ];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">SaaS beheeromgeving</p>
        <h1 class="dash-title">Welkom terug, {{ $user->name }}</h1>
        <p class="dash-subtitle">Beheer hier je jobboard software, licentie, tenant omgevingen en gekoppelde domeinen.</p>
      </div>
      <aside class="dash-user" aria-label="Ingelogde gebruiker">
        <strong>{{ $user->company_name ?: 'Jobboard account' }}</strong>
        <span>{{ $user->email }}</span>
        <span>Rol: SaaS gebruiker</span>
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
              <h2 id="owner-boards-title">Mijn jobboards</h2>
              <p>Omgevingen die straks op het domein van je klant of merk draaien.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">
              <i class="ph ph-plus"></i>
              Omgeving beheren
            </a>
          </div>

          @if($tenantCollection->isEmpty())
            <div class="dash-empty">
              <h3>Nog geen jobboard omgeving</h3>
              <p>Maak je eerste omgeving aan, koppel een domein en laat de vacaturefrontend daar live komen.</p>
              <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">Eerste omgeving aanmaken</a>
            </div>
          @else
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Jobboard</th>
                  <th>Plan</th>
                  <th>Status</th>
                  <th>Domeinen</th>
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
              <h2 id="owner-flow-title">Implementatie voortgang</h2>
              <p>De basisflow voor een klant die jobboard software koopt en publiceert.</p>
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
          <h2>Snelle acties</h2>
          <p>Ga direct naar de belangrijkste onderdelen van de SaaS omgeving.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('onboarding.index') }}">Onboarding</a>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.environments.index') }}">Omgevingen beheren</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('billing.index') }}">Licentie bekijken</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('pages.contact') }}">Support vragen</a>
          </div>
        </section>

        <section class="dash-card">
          <h2>Domein koppelen</h2>
          <p>De jobboard frontend wordt pas zichtbaar op het domein dat aan een tenant is gekoppeld.</p>
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
                <span>Wordt per domein voorbereid</span>
              </div>
              <span>Auto</span>
            </li>
          </ul>
        </section>

        <form method="POST" action="{{ route('logout') }}" class="dash-card">
          @csrf
          <h2>Sessie</h2>
          <p>Je bent ingelogd in de centrale SaaS beheeromgeving.</p>
          <div class="dash-actions dash-actions--spaced">
            <button class="dash-btn dash-btn--ghost" type="submit">Uitloggen</button>
          </div>
        </form>
      </aside>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
@endpush

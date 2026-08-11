@extends('layouts.app')

@section('title', 'Admin dashboard | JobBoardSoftware')
@section('meta_description', 'Admin dashboard voor SaaS gebruikers, tenants, domeinen, vacatures en sollicitaties.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('admin.partials.navigation')

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Admin</p>
          <h1 class="dash-title">Platformbeheer</h1>
          <p class="dash-subtitle">Overzicht van SaaS gebruikers, jobboard omgevingen, domeinen en activiteit.</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $user->name }}</strong>
          <span>{{ $user->email }}</span>
          <span>Admin dashboard</span>
        </aside>
      </header>

      <div class="dash-stats">
        @foreach($stats as $label => $value)
          <article class="dash-stat">
            <span>{{ ucfirst($label) }}</span>
            <strong>{{ $value }}</strong>
          </article>
        @endforeach
      </div>

      <div class="dash-layout">
        <main class="dash-main">
          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Recente tenants</h2>
                <p>Laatste jobboard omgevingen op het platform.</p>
              </div>
            </div>

            <table class="dash-table">
              <thead>
                <tr>
                  <th>Tenant</th>
                  <th>Eigenaar</th>
                  <th>Plan</th>
                  <th>Domeinen</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tenants as $tenant)
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $tenant->name }}</span>
                      <span class="dash-cell-meta">{{ $tenant->id }}</span>
                    </td>
                    <td>{{ $tenant->owner?->email ?? 'Geen eigenaar' }}</td>
                    <td>{{ ucfirst($tenant->plan) }}</td>
                    <td>{{ $tenant->domains->count() }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4">Nog geen tenants.</td></tr>
                @endforelse
              </tbody>
            </table>
          </section>
        </main>

        <aside class="dash-sidebar">
          <section class="dash-card">
            <h2>Domeinstatus</h2>
            <ul class="dash-list">
              @forelse($domains as $domain)
                <li>
                  <div>
                    <strong>{{ $domain->domain }}</strong>
                    <span>DNS: {{ ucfirst($domain->status) }} - SSL: {{ ucfirst($domain->ssl_status) }}</span>
                  </div>
                  <span>{{ $domain->tenant_id }}</span>
                </li>
              @empty
                <li>
                  <div>
                    <strong>Nog geen domeinen</strong>
                    <span>Klantdomeinen verschijnen hier.</span>
                  </div>
                </li>
              @endforelse
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
@endpush

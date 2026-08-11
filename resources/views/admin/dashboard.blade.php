@extends('layouts.app')

@section('title', 'Admin dashboard | JobBoardSoftware')
@section('meta_description', 'Admin dashboard for SaaS users, tenants, domains, jobs and applications.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('admin.partials.navigation')

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Admin</p>
          <h1 class="dash-title">Platform management</h1>
          <p class="dash-subtitle">Overview of SaaS users, job board environments, domains and activity.</p>
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
                <h2>Recent tenants</h2>
                <p>Latest job board environments on the platform.</p>
              </div>
            </div>

            <table class="dash-table">
              <thead>
                <tr>
                  <th>Tenant</th>
                  <th>Owner</th>
                  <th>Plan</th>
                  <th>Domains</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tenants as $tenant)
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $tenant->name }}</span>
                      <span class="dash-cell-meta">{{ $tenant->id }}</span>
                    </td>
                    <td>{{ $tenant->owner?->email ?? 'No owner' }}</td>
                    <td>{{ ucfirst($tenant->plan) }}</td>
                    <td>{{ $tenant->domains->count() }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4">No tenants yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </section>
        </main>

        <aside class="dash-sidebar">
          <section class="dash-card">
            <h2>Domain status</h2>
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
                    <strong>No domains yet</strong>
                    <span>Customer domains will appear here.</span>
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

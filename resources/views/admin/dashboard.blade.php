@extends('layouts.app')

@section('title', 'Admin dashboard | JobBoardSoftware')
@section('meta_description', 'Admin dashboard for SaaS users, tenants, domains, jobs and applications.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('admin.partials.navigation')
@endsection

@section('content')
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
                <h2>Admin sections</h2>
                <p>Jump directly into the operational tables.</p>
              </div>
            </div>
            <div class="admin-summary-list">
              <a href="{{ route('admin.users.index') }}"><strong>Users</strong><span>{{ $stats['users'] }}</span></a>
              <a href="{{ route('admin.tenants.index') }}"><strong>Tenants</strong><span>{{ $stats['tenants'] }}</span></a>
              <a href="{{ route('admin.domains.index') }}"><strong>Domains</strong><span>{{ $stats['domains'] }}</span></a>
              <a href="{{ route('admin.jobs.index') }}"><strong>Jobs</strong><span>{{ $stats['jobs'] }}</span></a>
              <a href="{{ route('admin.applications.index') }}"><strong>Applications</strong><span>{{ $stats['applications'] }}</span></a>
            </div>
          </section>

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

          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Recent applications</h2>
                <p>Latest candidate submissions across tenants.</p>
              </div>
            </div>

            <table class="dash-table">
              <thead>
                <tr>
                  <th>Candidate</th>
                  <th>Job</th>
                  <th>Tenant</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $application->name }}</span>
                      <span class="dash-cell-meta">{{ $application->email }}</span>
                    </td>
                    <td>{{ $application->job?->title ?? 'Deleted job' }}</td>
                    <td>{{ $application->tenant?->name ?? $application->tenant_id }}</td>
                    <td><span class="dash-status">{{ ucfirst($application->status) }}</span></td>
                  </tr>
                @empty
                  <tr><td colspan="4">No applications yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </section>
        </main>

        <aside class="dash-sidebar">
          <section class="dash-card">
            <h2>Recent users</h2>
            <ul class="dash-list">
              @forelse($users as $managedUser)
                <li>
                  <div>
                    <strong>{{ $managedUser->name }}</strong>
                    <span>{{ $managedUser->email }}</span>
                  </div>
                  <span>{{ $managedUser->role }}</span>
                </li>
              @empty
                <li>
                  <div>
                    <strong>No users yet</strong>
                    <span>New registrations will appear here.</span>
                  </div>
                </li>
              @endforelse
            </ul>
          </section>

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
@endsection

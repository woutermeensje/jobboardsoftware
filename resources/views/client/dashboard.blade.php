@extends('layouts.app')

@section('title', 'Client dashboard | JobBoardSoftware')
@section('meta_description', 'Client dashboard for managing job board environments, domains, jobs and applications.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('client.partials.navigation')

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Client dashboard</p>
          <h1 class="dash-title">Your job boards</h1>
          <p class="dash-subtitle">A custom dashboard shell for environments, domains, jobs and applications.</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $user->name }}</strong>
          <span>{{ $user->email }}</span>
          <span>{{ str($user->role)->headline() }}</span>
        </aside>
      </header>

      <div class="dash-stats">
        @foreach($stats as $label => $value)
          <article class="dash-stat">
            <span>{{ str($label)->headline() }}</span>
            <strong>{{ $value }}</strong>
          </article>
        @endforeach
      </div>

      <div class="dash-layout">
        <main class="dash-main">
          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Workspace sections</h2>
                <p>These client dashboard routes are ready for your own custom UI.</p>
              </div>
            </div>
            <div class="admin-summary-list">
              <a href="{{ route('client.environments.index') }}"><strong>Environments</strong><span>{{ $stats['environments'] }}</span></a>
              <a href="{{ route('client.jobs.index') }}"><strong>Jobs</strong><span>{{ $stats['jobs'] }}</span></a>
              <a href="{{ route('client.domains.index') }}"><strong>Domains</strong><span>{{ $stats['domains'] }}</span></a>
              <a href="{{ route('client.applications.index') }}"><strong>Applications</strong><span>{{ $stats['applications'] }}</span></a>
            </div>
          </section>

          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Environments</h2>
                <p>Current job boards owned by this account.</p>
              </div>
              <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
            </div>

            <table class="dash-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Plan</th>
                  <th>Domains</th>
                  <th>Jobs</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tenants as $tenant)
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $tenant->name }}</span>
                      <span class="dash-cell-meta">{{ $tenant->slug }}</span>
                    </td>
                    <td>{{ ucfirst($tenant->plan) }}</td>
                    <td>{{ $tenant->domains->count() }}</td>
                    <td>{{ $tenant->jobs_count }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4">No environments yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </section>

          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Recent jobs</h2>
                <p>Latest vacancies across your job boards.</p>
              </div>
              <a class="dash-link" href="{{ route('client.jobs.create') }}">Create job</a>
            </div>

            <table class="dash-table">
              <thead>
                <tr>
                  <th>Job</th>
                  <th>Tenant</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($jobs as $job)
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $job->title }}</span>
                      <span class="dash-cell-meta">{{ $job->location ?: 'No location' }}</span>
                    </td>
                    <td>{{ $job->tenant_id }}</td>
                    <td><span class="dash-status">{{ ucfirst($job->status) }}</span></td>
                  </tr>
                @empty
                  <tr><td colspan="3">No jobs yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </section>
        </main>

        <aside class="dash-sidebar">
          <section class="dash-card">
            <h2>Domains</h2>
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
                    <span>Connected domains will appear here.</span>
                  </div>
                </li>
              @endforelse
            </ul>
          </section>

          <section class="dash-card">
            <h2>Applications</h2>
            <ul class="dash-list">
              @forelse($applications as $application)
                <li>
                  <div>
                    <strong>{{ $application->name }}</strong>
                    <span>{{ $application->email }}</span>
                  </div>
                  <span>{{ ucfirst($application->status) }}</span>
                </li>
              @empty
                <li>
                  <div>
                    <strong>No applications yet</strong>
                    <span>Candidate applications will appear here.</span>
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
  @include('admin.partials.styles')
@endpush

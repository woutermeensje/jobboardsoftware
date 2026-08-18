@extends('layouts.app')

@section('title', 'Environments | Client dashboard')
@section('meta_description', 'Manage your job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success">
          {{ session('status') }}
        </section>
      @endif

      <section class="dash-panel dash-panel--list">
        <div class="dash-panel__head">
          <div>
            <h2>Environments</h2>
            <p>Job board environments owned by this account.</p>
          </div>
          <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
        </div>

        <div class="dash-panel__body">
          @if($tenants->isEmpty())
            <div class="dash-empty">
              <h3>No environments yet</h3>
              <p>Create your first job board environment to get started.</p>
              <div class="dash-actions">
                <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
              </div>
            </div>
          @else
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Environment</th>
                  <th>Domain</th>
                  <th>Plan</th>
                  <th>Status</th>
                  <th>Jobs</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($tenants as $tenant)
                  @php
                    $primaryDomain = $tenant->domains->firstWhere('is_primary', true) ?? $tenant->domains->first();
                    $isActive = $activeTenantId === $tenant->id;
                  @endphp
                  <tr>
                    <td>
                      <span class="dash-cell-title">
                        {{ $tenant->name }}
                        @if($isActive)
                          <span class="dash-status dash-status--muted">Active</span>
                        @endif
                      </span>
                      <span class="dash-cell-meta">{{ $tenant->slug }}</span>
                    </td>
                    <td>
                      @if($primaryDomain)
                        <span class="dash-cell-title">{{ $primaryDomain->domain }}</span>
                        <span class="dash-cell-meta">{{ $primaryDomain->isReadyForTraffic() ? 'Live' : 'Pending verification' }}</span>
                      @else
                        <span class="dash-cell-meta">No domain connected</span>
                      @endif
                    </td>
                    <td>{{ ucfirst($tenant->plan) }}</td>
                    <td><span class="dash-status">{{ ucfirst($tenant->status) }}</span></td>
                    <td>{{ $tenant->jobs_count }}</td>
                    <td>
                      <div class="dash-actions">
                        @if($primaryDomain?->isReadyForTraffic())
                          <a class="dash-link" href="https://{{ $primaryDomain->domain }}" target="_blank" rel="noopener">Visit</a>
                        @else
                          <a class="dash-link" href="{{ route('client.domains.index') }}">Manage domain</a>
                        @endif

                        @unless($isActive)
                          <form method="POST" action="{{ route('client.environments.activate', $tenant) }}">
                            @csrf
                            <button class="dash-btn dash-btn--ghost btn-sm" type="submit">Set as active</button>
                          </form>
                        @endunless

                        <form method="POST" action="{{ route('client.environments.destroy', $tenant) }}" onsubmit="return confirm('Delete {{ addslashes($tenant->name) }}? This removes all its jobs, applications, companies and domains. This cannot be undone.');">
                          @csrf
                          @method('DELETE')
                          <button class="dash-btn dash-btn--ghost btn-sm dash-btn--danger" type="submit">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </section>
@endsection

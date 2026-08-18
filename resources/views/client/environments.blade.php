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
                  @endphp
                  <tr>
                    <td>
                      <span class="dash-cell-title">{{ $tenant->name }}</span>
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
                      @if($primaryDomain?->isReadyForTraffic())
                        <a class="dash-link" href="https://{{ $primaryDomain->domain }}" target="_blank" rel="noopener">Visit</a>
                      @else
                        <a class="dash-link" href="{{ route('client.domains.index') }}">Manage domain</a>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </section>
@endsection

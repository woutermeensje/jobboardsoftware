@extends('layouts.app')

@section('title', 'Jobs | Client dashboard')
@section('meta_description', 'Manage published and draft jobs for job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>Jobs</h2>
            <p>Manage published and draft jobs for your job board environments.</p>
          </div>
          <a class="dash-link" href="{{ route('client.jobs.create') }}">Create job</a>
        </div>

        @if($jobs->isEmpty())
          <div class="dash-empty">
            <h3>No jobs yet</h3>
            <p>Published and draft jobs will appear here.</p>
            <div class="dash-actions">
              <a class="dash-link" href="{{ route('client.jobs.create') }}">Create job</a>
            </div>
          </div>
        @else
          <table class="dash-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Company</th>
                <th>Environment</th>
                <th>Status</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              @foreach($jobs as $job)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $job->title }}</span>
                    <span class="dash-cell-meta">{{ $job->location }}</span>
                  </td>
                  <td>{{ $job->company?->name ?? $job->company_name ?? 'No company' }}</td>
                  <td>
                    <span class="dash-cell-title">{{ $job->tenant?->name ?? $job->tenant_id }}</span>
                    <span class="dash-cell-meta">{{ $job->tenant?->slug }}</span>
                  </td>
                  <td>
                    <span class="dash-status {{ $job->status === \App\Models\TenantJob::STATUS_DRAFT ? 'dash-status--muted' : '' }}">
                      {{ $job->status === \App\Models\TenantJob::STATUS_PUBLISHED ? 'Published' : 'Draft' }}
                    </span>
                  </td>
                  <td>{{ $job->created_at?->format('M j, Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </section>
@endsection

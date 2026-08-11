@extends('layouts.app')

@section('title', 'Manage jobs | JobBoardSoftware')
@section('meta_description', 'Manage jobs for a tenant job board.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation', ['activeTenant' => $tenant])

    <div class="dash-content">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">{{ $tenant->name }}</p>
        <h1 class="dash-title">Manage jobs</h1>
        <p class="dash-subtitle">Create jobs, publish them on the customer domain and follow up on applications.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ $jobs->count() }} jobs</strong>
        <span>{{ $tenant->slug }}</span>
        <span>{{ ucfirst($tenant->status) }}</span>
      </aside>
    </header>

    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel">
      <div class="dash-panel__head">
        <div>
          <h2>Jobs</h2>
          <p>Draft and published jobs for this environment.</p>
        </div>
        <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs.create', $tenant) }}">
          <i class="ph ph-plus"></i>
          New job
        </a>
      </div>

      @if($jobs->isEmpty())
        <div class="dash-empty">
          <h3>No jobs yet</h3>
          <p>Create your first job to populate the tenant frontend.</p>
          <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs.create', $tenant) }}">Create job</a>
        </div>
      @else
        <table class="dash-table">
          <thead>
            <tr>
              <th>Job</th>
              <th>Status</th>
              <th>Applications</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jobs as $job)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $job->title }}</span>
                  <span class="dash-cell-meta">{{ $job->department }} - {{ $job->location }} - {{ $job->employment_type }}</span>
                </td>
                <td><span class="dash-status {{ $job->status !== \App\Models\TenantJob::STATUS_PUBLISHED ? 'dash-status--accent' : '' }}">{{ ucfirst($job->status) }}</span></td>
                <td>{{ $job->applications_count }}</td>
                <td>
                  <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.jobs.edit', [$tenant, $job]) }}">Edit</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </section>
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
  </style>
@endpush

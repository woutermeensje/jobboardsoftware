@extends('layouts.app')

@section('title', 'Jobs | JobBoardSoftware admin')
@section('meta_description', 'Manage tenant jobs across the platform.')

@php
  $jobStatuses = [
    \App\Models\TenantJob::STATUS_DRAFT => 'Draft',
    \App\Models\TenantJob::STATUS_PUBLISHED => 'Published',
    \App\Models\TenantJob::STATUS_CLOSED => 'Closed',
  ];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('admin.partials.navigation')

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Admin</p>
          <h1 class="dash-title">Jobs</h1>
          <p class="dash-subtitle">Monitor and moderate jobs across all tenant environments.</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $jobs->count() }} jobs</strong>
          <span>{{ $user->email }}</span>
          <span>Job management</span>
        </aside>
      </header>

      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>All jobs</h2>
            <p>Change publication status without entering the tenant owner portal.</p>
          </div>
        </div>

        <table class="dash-table">
          <thead>
            <tr>
              <th>Job</th>
              <th>Tenant</th>
              <th>Status</th>
              <th>Applications</th>
              <th>Manage</th>
            </tr>
          </thead>
          <tbody>
            @forelse($jobs as $job)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $job->title }}</span>
                  <span class="dash-cell-meta">{{ $job->slug }}</span>
                  <span class="dash-cell-meta">{{ $job->location ?: 'No location' }} - {{ $job->employment_type ?: 'No type' }}</span>
                </td>
                <td>
                  <span class="dash-cell-title">{{ $job->tenant?->name ?? $job->tenant_id }}</span>
                  <span class="dash-cell-meta">{{ $job->tenant?->owner?->email ?? 'Unknown owner' }}</span>
                </td>
                <td>
                  <span class="dash-status">{{ ucfirst($job->status) }}</span>
                  <span class="dash-cell-meta">Published: {{ $job->published_at?->format('Y-m-d H:i') ?? 'No' }}</span>
                </td>
                <td>{{ $job->applications_count }}</td>
                <td>
                  <form class="admin-table-form" method="POST" action="{{ route('admin.jobs.update', $job) }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-form-grid">
                      <select class="form-control" name="status" aria-label="Job status" required>
                        @foreach($jobStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('status', $job->status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <button class="dash-btn dash-btn--primary" type="submit">Save job</button>
                    </div>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5">No jobs yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </section>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
  @include('admin.partials.styles')
@endpush

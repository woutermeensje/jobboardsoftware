@extends('layouts.tenant')

@section('title', ($tenant->name ?? 'Jobboard').' | Jobs')
@section('meta_description', 'Jobs, filters and applications for this job board.')

@php
  $settings = $tenant->settings ?? [];
  $brandName = $settings['brand_name'] ?? $tenant->name ?? 'Jobboard';
  $intro = $settings['intro'] ?? 'View current jobs and apply directly.';
@endphp

@section('content')
  <main class="tenant-shell">
    <section class="tenant-hero">
      <div>
        <p class="tenant-eyebrow">Job platform</p>
        <h1>{{ $brandName }}</h1>
        <p>{{ $intro }}</p>
        <div class="tenant-actions">
          <a class="tenant-btn tenant-btn--primary" href="#jobs">View jobs</a>
          <a class="tenant-btn tenant-btn--ghost" href="#contact">Contact</a>
        </div>
      </div>
      <aside class="tenant-card">
        <span>Open roles</span>
        <strong>{{ $jobs->count() }}</strong>
        <p>Plan: {{ ucfirst($tenant->plan ?? 'starter') }}</p>
      </aside>
    </section>

    <section class="tenant-panel" id="jobs" aria-labelledby="tenant-jobs-title">
      <div class="tenant-jobs-overview__head">
        <div>
          <p class="tenant-eyebrow">Jobs</p>
          <h2 id="tenant-jobs-title">Open roles</h2>
          <p>Explore the current openings and filter by team, location or employment type.</p>
        </div>

        <div class="tenant-jobs-summary" aria-label="Open roles summary">
          <strong>{{ $jobs->count() }}</strong>
          <span>{{ $jobs->count() === 1 ? 'matching role' : 'matching roles' }}</span>
          <small>{{ $totalJobs }} {{ $totalJobs === 1 ? 'role total' : 'roles total' }}</small>
        </div>
      </div>

      @include('tenant.components.job-filters')

      <div class="tenant-jobs">
        @forelse($jobs as $job)
          @include('tenant.components.job-card', ['job' => $job])
        @empty
          <article class="tenant-jobs-empty">
            <i class="ph ph-briefcase" aria-hidden="true"></i>
            <h3>No jobs found</h3>
            <p>Adjust your filters or come back later.</p>
            @if(request()->hasAny(['search', 'department', 'location', 'employment_type']))
              <a class="tenant-btn tenant-btn--ghost" href="{{ route('tenant.jobs') }}#jobs">Reset filters</a>
            @endif
          </article>
        @endforelse
      </div>
    </section>

    <section class="tenant-panel" id="contact" aria-labelledby="tenant-contact-title">
      <p class="tenant-eyebrow">Contact</p>
      <h2 id="tenant-contact-title">Apply or want to know more?</h2>
      <p>Apply directly for a job or contact the recruitment team at {{ $brandName }}.</p>
    </section>
  </main>
@endsection

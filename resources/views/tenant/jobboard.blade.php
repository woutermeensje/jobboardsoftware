@extends('layouts.tenant')

@section('title', ($tenant->name ?? 'Jobboard').' | Jobs')
@section('meta_description', 'Jobs, filters and applications for this job board.')

@php
  $settings = $tenant->settings ?? [];
  $brandName = $settings['brand_name'] ?? $tenant->name ?? 'Jobboard';
  $accent = $settings['accent_color'] ?? '#2f5f80';
  $intro = $settings['intro'] ?? 'View current jobs and apply directly.';
@endphp

@section('content')
<section class="tenant-page" style="--tenant-accent: {{ $accent }};">
  <header class="tenant-nav">
    <a class="tenant-brand" href="{{ route('tenant.home') }}">
      <span>{{ mb_substr($brandName, 0, 1) }}</span>
      <strong>{{ $brandName }}</strong>
    </a>
    <nav>
      <a href="{{ route('tenant.jobs') }}">Jobs</a>
      <a href="{{ route('tenant.contact') }}">Contact</a>
    </nav>
  </header>

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
      <div class="tenant-panel__head">
        <div>
          <p class="tenant-eyebrow">Jobs</p>
          <h2 id="tenant-jobs-title">Open roles</h2>
        </div>
      </div>

      <form class="tenant-filter" method="GET" action="{{ route('tenant.jobs') }}">
        <input name="search" value="{{ request('search') }}" placeholder="Search by title, department or location">
        <select name="department">
          <option value="">All departments</option>
          @foreach($departments as $department)
            <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
          @endforeach
        </select>
        <button type="submit">Search</button>
      </form>

      <div class="tenant-jobs">
        @forelse($jobs as $job)
          <article class="tenant-job">
            <div>
              <h3>{{ $job->title }}</h3>
              <p>{{ $job->department }} - {{ $job->location }} - {{ $job->employment_type }}</p>
              @if($job->intro)
                <span>{{ $job->intro }}</span>
              @endif
            </div>
            <a href="{{ route('tenant.jobs.show', $job) }}">View job</a>
          </article>
        @empty
          <article class="tenant-job tenant-job--empty">
            <div>
              <h3>No jobs found</h3>
              <p>Adjust your filters or come back later.</p>
            </div>
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
</section>
@endsection

@push('styles')
  @include('tenant.partials.styles')
@endpush

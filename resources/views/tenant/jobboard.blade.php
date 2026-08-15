@extends('layouts.tenant')

@section('title', ($tenant->name ?? 'Jobboard').' | Jobs')
@section('meta_description', 'Jobs, filters and applications for this job board.')

@php
  $settings = $tenant->settings ?? [];
  $brandName = $settings['brand_name'] ?? $tenant->name ?? 'Jobboard';
  $selectedDepartments = collect((array) request('department'))->filter()->values();
  $selectedEmploymentTypes = collect((array) request('employment_type'))->filter()->values();
  $hasActiveJobFilters = request()->filled('search')
    || request()->filled('location')
    || $selectedDepartments->isNotEmpty()
    || $selectedEmploymentTypes->isNotEmpty();
@endphp

@section('content')
  <main class="tenant-shell">
    <section class="tenant-jobs-index" id="jobs" aria-labelledby="tenant-jobs-title">
      <header class="tenant-jobs-filter-wrap" aria-label="Job filters">
        <div class="tenant-jobs-filter-header">
          <h2 id="tenant-jobs-title">Doorzoek alle vacatures</h2>
          <p>Vacatures, stages en functies binnen {{ $brandName }}.</p>
        </div>

        @include('tenant.components.job-filters', [
          'selectedDepartments' => $selectedDepartments,
          'selectedEmploymentTypes' => $selectedEmploymentTypes,
        ])
      </header>

      <div class="tenant-jobs-section-divider"></div>

      <div class="tenant-jobs-results-head">
        <p class="tenant-jobs-results-count">
          {{ $jobs->count() }} {{ $jobs->count() === 1 ? 'vacature' : 'vacatures' }} gevonden
          <span>{{ $totalJobs }} {{ $totalJobs === 1 ? 'vacature totaal' : 'vacatures totaal' }}</span>
        </p>

        @if($hasActiveJobFilters)
          <a class="tenant-jobs-reset-link" href="{{ route('tenant.jobs') }}#jobs">Wis alle filters</a>
        @endif
      </div>

      <div class="tenant-jobs-content-layout">
        <aside class="tenant-jobs-sidebar" aria-label="Sidebar filters">
          @include('tenant.components.job-sidebar-filters', [
            'selectedDepartments' => $selectedDepartments,
            'selectedEmploymentTypes' => $selectedEmploymentTypes,
            'hasActiveJobFilters' => $hasActiveJobFilters,
          ])
        </aside>

        <section class="tenant-jobs-results-section" aria-label="Job results">
          <div class="tenant-jobs">
            @forelse($jobs as $job)
              @include('tenant.components.job-card', ['job' => $job])
            @empty
              <article class="tenant-jobs-empty">
                <i class="ph ph-briefcase" aria-hidden="true"></i>
                <h3>Geen vacatures gevonden</h3>
                <p>Pas je filters aan of reset de zoekopdracht om meer resultaten te zien.</p>
                @if($hasActiveJobFilters)
                  <a class="tenant-btn tenant-btn--ghost" href="{{ route('tenant.jobs') }}#jobs">Reset filters</a>
                @endif
              </article>
            @endforelse
          </div>
        </section>
      </div>
    </section>

    <section class="tenant-panel" id="contact" aria-labelledby="tenant-contact-title">
      <p class="tenant-eyebrow">Contact</p>
      <h2 id="tenant-contact-title">Apply or want to know more?</h2>
      <p>Apply directly for a job or contact the recruitment team at {{ $brandName }}.</p>
    </section>
  </main>
@endsection

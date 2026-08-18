@extends('layouts.tenant')

@section('title', ($tenant->name ?? 'Jobboard').' | Companies')
@section('meta_description', 'Browse all companies hiring on '.$brandName.'.')

@section('content')
  <main class="tenant-shell">
    <section class="tenant-jobs-index" id="companies" aria-labelledby="tenant-companies-title">
      <header class="tenant-jobs-filter-wrap" aria-label="Companies intro">
        <div class="tenant-jobs-filter-header">
          <h2 id="tenant-companies-title">Companies</h2>
          <p>Browse all companies hiring on {{ $brandName }}.</p>
        </div>
      </header>

      <div class="tenant-jobs-section-divider"></div>

      <div class="tenant-jobs-results-head">
        <p class="tenant-jobs-results-count">
          {{ $companies->count() }} {{ $companies->count() === 1 ? 'company' : 'companies' }} found
        </p>
      </div>

      <div class="tenant-jobs">
        @forelse($companies as $company)
          @include('tenant.components.company-card', ['company' => $company])
        @empty
          <article class="tenant-jobs-empty">
            <i class="ph ph-buildings" aria-hidden="true"></i>
            <h3>No companies yet</h3>
            <p>Check back soon.</p>
          </article>
        @endforelse
      </div>
    </section>
  </main>
@endsection

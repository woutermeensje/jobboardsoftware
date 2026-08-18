@extends('layouts.tenant')

@php
  $companyName = $company->organization_name ?: $company->name;
  $companyLogoUrl = \App\Support\PublicUploadStorage::url($company->logo_path);
  $logoInitial = mb_strtoupper(mb_substr((string) $companyName, 0, 1));
  $companyUrl = $company->company_url ?: null;
  $sectorValues = $company->sectorValues();
  $organizationTypeValues = $company->organizationTypeValues();

  if ($companyUrl && ! \Illuminate\Support\Str::startsWith($companyUrl, ['http://', 'https://'])) {
    $companyUrl = 'https://'.$companyUrl;
  }
@endphp

@section('title', $companyName.' | '.($tenant->name ?? 'Jobboard'))
@section('meta_description', $company->description ? trim(strip_tags($company->description)) : 'View jobs at '.$companyName.'.')

@section('content')
  <main class="tenant-single-job">
    <nav class="tenant-single-job__breadcrumbs" aria-label="Breadcrumb">
      <ol>
        <li>
          <a href="{{ route('tenant.home') }}">Home</a>
        </li>
        <li>
          <a href="{{ route('tenant.companies') }}">Companies</a>
        </li>
        <li aria-current="page">{{ $companyName }}</li>
      </ol>
    </nav>

    <div class="tenant-single-job__layout">
      <div class="tenant-single-job__main">
        <article class="tenant-single-job__content" aria-labelledby="tenant-single-company-page-title">
          <header class="tenant-single-job__title-row">
            <div class="tenant-single-job__title-content">
              <h1 id="tenant-single-company-page-title">{{ $companyName }}</h1>
            </div>
          </header>

          @if($company->description)
            <div class="tenant-single-job__description rich-text">
              {!! $company->description !!}
            </div>
          @endif
        </article>

        <section class="tenant-single-job__question" aria-labelledby="tenant-company-jobs-title">
          <h2 id="tenant-company-jobs-title">Open jobs at {{ $companyName }}</h2>

          <div class="tenant-jobs">
            @forelse($jobs as $job)
              @include('tenant.components.job-card', ['job' => $job])
            @empty
              <article class="tenant-jobs-empty">
                <i class="ph ph-briefcase" aria-hidden="true"></i>
                <h3>No open jobs</h3>
                <p>{{ $companyName }} doesn't have any open positions right now.</p>
              </article>
            @endforelse
          </div>
        </section>
      </div>

      <aside class="tenant-single-job__sidebar" aria-label="Company sidebar">
        <section class="tenant-single-job__sidebar-card" aria-labelledby="tenant-single-company-title">
          <p class="tenant-single-job__sidebar-title" id="tenant-single-company-title">About</p>

          <div class="tenant-single-job__company">
            <span class="tenant-single-job__company-logo @unless($companyLogoUrl) tenant-single-job__company-logo--empty @endunless" aria-hidden="true">
              <span>{{ $logoInitial }}</span>
              @if($companyLogoUrl)
                <img
                  src="{{ $companyLogoUrl }}"
                  alt=""
                  onerror="this.hidden = true; this.parentElement.classList.add('tenant-single-job__company-logo--empty');"
                >
              @endif
            </span>

            <div class="tenant-single-job__company-info">
              @if($companyUrl)
                <a class="tenant-single-job__company-name" href="{{ $companyUrl }}" target="_blank" rel="noopener">{{ $companyName }}</a>
              @else
                <span class="tenant-single-job__company-name">{{ $companyName }}</span>
              @endif

              <span class="tenant-single-job__company-count">
                {{ $jobs->count() }} {{ $jobs->count() === 1 ? 'open job' : 'open jobs' }}
              </span>
            </div>
          </div>
        </section>

        @if($sectorValues !== [] || $organizationTypeValues !== [])
          <section class="tenant-single-job__sidebar-card" aria-labelledby="tenant-single-company-details-title">
            <p class="tenant-single-job__sidebar-title" id="tenant-single-company-details-title">Company details</p>

            <div class="tenant-single-job__details">
              @if($sectorValues !== [])
                <div class="tenant-single-job__detail-row">
                  <span class="tenant-single-job__detail-label">Sector</span>
                  <span class="tenant-single-job__detail-value">{{ implode(', ', $sectorValues) }}</span>
                </div>
              @endif

              @if($organizationTypeValues !== [])
                <div class="tenant-single-job__detail-row">
                  <span class="tenant-single-job__detail-label">Organization type</span>
                  <span class="tenant-single-job__detail-value">{{ implode(', ', $organizationTypeValues) }}</span>
                </div>
              @endif
            </div>
          </section>
        @endif

        @if($company->contact_name || $company->contact_email || $company->contact_phone)
          <section class="tenant-single-job__sidebar-card" aria-labelledby="tenant-single-company-contact-title">
            <p class="tenant-single-job__sidebar-title" id="tenant-single-company-contact-title">Contact</p>

            <div class="tenant-single-job__contact">
              @if($company->contact_name)
                <p class="tenant-single-job__contact-name">{{ $company->contact_name }}</p>
              @endif

              @if($company->contact_email)
                <a class="tenant-single-job__contact-link" href="mailto:{{ $company->contact_email }}">{{ $company->contact_email }}</a>
              @endif

              @if($company->contact_phone)
                <a class="tenant-single-job__contact-link" href="tel:{{ $company->contact_phone }}">{{ $company->contact_phone }}</a>
              @endif
            </div>
          </section>
        @endif
      </aside>
    </div>
  </main>
@endsection

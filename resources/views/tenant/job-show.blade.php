@extends('layouts.tenant')

@php
  $jobIntroText = trim(strip_tags((string) $job->intro));
  $company = $job->relationLoaded('company') ? $job->company : null;
  $companyName = $job->company_name ?: ($company?->name ?? null);
  $companyLogoPath = $job->company_logo_path ?: ($company?->logo_path ?? null);
  $companyLogoUrl = \App\Support\PublicUploadStorage::url($companyLogoPath);
  $logoInitial = mb_strtoupper(mb_substr((string) ($companyName ?: $job->title), 0, 1));
  $publishedAt = $job->published_at ?? $job->created_at;
  $postedDate = $publishedAt ? $publishedAt->format('F j, Y') : null;
  $companyVacancyCount = $companyVacancyCount ?? null;
  $employmentTypes = collect(explode(',', (string) $job->employment_type))
    ->map(fn (string $type): string => trim($type))
    ->filter()
    ->values();
  $companyUrl = $job->company_url ?: null;
  $jobUrl = $job->job_url ?: null;

  if ($companyUrl && ! \Illuminate\Support\Str::startsWith($companyUrl, ['http://', 'https://'])) {
    $companyUrl = 'https://'.$companyUrl;
  }

  if ($jobUrl && ! \Illuminate\Support\Str::startsWith($jobUrl, ['http://', 'https://'])) {
    $jobUrl = 'https://'.$jobUrl;
  }
@endphp

@section('title', $job->title.' | '.($tenant->name ?? 'Jobboard'))
@section('meta_description', $jobIntroText !== '' ? $jobIntroText : 'View this job and apply directly.')

@section('content')
  <main class="tenant-single-job">
    @if(session('status'))
      <section class="tenant-alert">{{ session('status') }}</section>
    @endif

    <nav class="tenant-single-job__breadcrumbs" aria-label="Breadcrumb">
      <ol>
        <li>
          <a href="{{ route('tenant.home') }}">Home</a>
        </li>
        <li>
          <a href="{{ route('tenant.jobs') }}">Jobs</a>
        </li>
        <li aria-current="page">{{ $job->title }}</li>
      </ol>
    </nav>

    <div class="tenant-single-job__layout">
      <div class="tenant-single-job__main">
        <article class="tenant-single-job__content" aria-labelledby="tenant-single-job-title">
          <header class="tenant-single-job__title-row">
            <div class="tenant-single-job__title-content">
              <h1 id="tenant-single-job-title">{{ $job->title }}</h1>

              @if($postedDate)
                <time class="tenant-single-job__posted-date" datetime="{{ $publishedAt->toDateString() }}">
                  Posted on {{ $postedDate }}
                </time>
              @endif
            </div>
          </header>

          @if($job->intro)
            <div class="tenant-single-job__intro rich-text">
              {!! $job->intro !!}
            </div>
          @endif

          <div class="tenant-single-job__description rich-text">
            {!! $job->description !!}
          </div>

          <div class="tenant-single-job__content-cta">
            @if($jobUrl)
              <a class="tenant-btn tenant-btn--primary" href="{{ $jobUrl }}" target="_blank" rel="noopener">
                Apply on company website
              </a>
            @else
              <a class="tenant-btn tenant-btn--primary" href="#tenant-apply">
                Apply for this job
              </a>
            @endif
          </div>
        </article>

        <section class="tenant-single-job__question" id="tenant-apply" aria-labelledby="tenant-apply-title">
          <h2 id="tenant-apply-title">Apply for this job</h2>

          <form method="POST" action="{{ route('tenant.jobs.apply', $job) }}" enctype="multipart/form-data">
            @csrf

            <div class="tenant-single-job__form-row">
              <label>
                Name
                <input name="name" value="{{ old('name') }}" required>
                @error('name')<span class="tenant-single-job__form-error">{{ $message }}</span>@enderror
              </label>

              <label>
                Email address
                <input name="email" type="email" value="{{ old('email') }}" required>
                @error('email')<span class="tenant-single-job__form-error">{{ $message }}</span>@enderror
              </label>
            </div>

            <label>
              Phone
              <input name="phone" value="{{ old('phone') }}">
            </label>

            <label>
              Motivation
              <textarea name="motivation" rows="5">{{ old('motivation') }}</textarea>
            </label>

            <label>
              Upload CV
              <input name="cv" type="file" accept=".pdf,.doc,.docx">
              @error('cv')<span class="tenant-single-job__form-error">{{ $message }}</span>@enderror
            </label>

            <button class="tenant-btn tenant-btn--primary" type="submit">Submit application</button>
          </form>
        </section>
      </div>

      <aside class="tenant-single-job__sidebar" aria-label="Job sidebar">
        @if($companyName || $companyLogoUrl)
          <section class="tenant-single-job__sidebar-card" aria-labelledby="tenant-single-company-title">
            <p class="tenant-single-job__sidebar-title" id="tenant-single-company-title">About the company</p>

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
                @if($companyUrl && $companyName)
                  <a class="tenant-single-job__company-name" href="{{ $companyUrl }}" target="_blank" rel="noopener">{{ $companyName }}</a>
                @elseif($companyName)
                  <span class="tenant-single-job__company-name">{{ $companyName }}</span>
                @endif

                @if($companyVacancyCount)
                  <span class="tenant-single-job__company-count">
                    {{ $companyVacancyCount }} {{ $companyVacancyCount === 1 ? 'open job' : 'open jobs' }}
                  </span>
                @endif
              </div>
            </div>
          </section>
        @endif

        <section class="tenant-single-job__sidebar-card" aria-labelledby="tenant-single-details-title">
          <p class="tenant-single-job__sidebar-title" id="tenant-single-details-title">Job details</p>

          <div class="tenant-single-job__details">
            @if($companyName)
              <div class="tenant-single-job__detail-row">
                <span class="tenant-single-job__detail-label">Company</span>
                <span class="tenant-single-job__detail-value">{{ $companyName }}</span>
              </div>
            @endif

            @if($employmentTypes->isNotEmpty())
              <div class="tenant-single-job__detail-row">
                <span class="tenant-single-job__detail-label">Job type</span>
                <span class="tenant-single-job__detail-value tenant-single-job__chips">
                  @foreach($employmentTypes as $type)
                    <span>{{ $type }}</span>
                  @endforeach
                </span>
              </div>
            @endif

            @if($job->salary_range)
              <div class="tenant-single-job__detail-row">
                <span class="tenant-single-job__detail-label">Salary</span>
                <span class="tenant-single-job__detail-value">{{ $job->salary_range }}</span>
              </div>
            @endif

            @if($job->location)
              <div class="tenant-single-job__detail-row">
                <span class="tenant-single-job__detail-label">Location</span>
                <span class="tenant-single-job__detail-value">{{ $job->location }}</span>
              </div>
            @endif

            @if($job->department)
              <div class="tenant-single-job__detail-row">
                <span class="tenant-single-job__detail-label">Category</span>
                <span class="tenant-single-job__detail-value">{{ $job->department }}</span>
              </div>
            @endif
          </div>

          <div class="tenant-single-job__sidebar-cta">
            @if($jobUrl)
              <a class="tenant-btn tenant-btn--primary" href="{{ $jobUrl }}" target="_blank" rel="noopener">
                Apply on company website
              </a>
            @else
              <a class="tenant-btn tenant-btn--primary" href="#tenant-apply">
                Apply for this job
              </a>
            @endif
          </div>
        </section>

        @if($job->contact_name || $job->contact_email || $job->contact_phone)
          <section class="tenant-single-job__sidebar-card" aria-labelledby="tenant-single-contact-title">
            <p class="tenant-single-job__sidebar-title" id="tenant-single-contact-title">Contact person</p>

            <div class="tenant-single-job__contact">
              @if($job->contact_name)
                <p class="tenant-single-job__contact-name">{{ $job->contact_name }}</p>
              @endif

              @if($job->contact_email)
                <a class="tenant-single-job__contact-link" href="mailto:{{ $job->contact_email }}">{{ $job->contact_email }}</a>
              @endif

              @if($job->contact_phone)
                <a class="tenant-single-job__contact-link" href="tel:{{ $job->contact_phone }}">{{ $job->contact_phone }}</a>
              @endif
            </div>
          </section>
        @endif
      </aside>
    </div>
  </main>
@endsection

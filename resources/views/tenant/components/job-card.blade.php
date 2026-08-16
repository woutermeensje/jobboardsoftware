@php
  $company = $job->relationLoaded('company') ? $job->company : null;
  $companyName = $job->company_name ?: ($company?->name ?? null);
  $companyLogoPath = $job->company_logo_path ?: ($company?->logo_path ?? null);
  $publishedAt = $job->published_at ?? $job->created_at;
  $daysAgo = $publishedAt ? max(1, (int) $publishedAt->diffInDays(now())) : null;
  $employmentTypes = collect(explode(',', (string) $job->employment_type))
    ->map(fn (string $type): string => trim($type))
    ->filter()
    ->values();
  $tagLabels = collect([$job->department])
    ->merge($employmentTypes)
    ->filter()
    ->unique(fn (string $tag): string => mb_strtolower($tag))
    ->values();
@endphp

<article class="tenant-job-card @if($companyLogoPath) tenant-job-card--has-logo @endif">
  @if($companyLogoPath)
    <span class="tenant-job-card__logo" aria-hidden="true">
      <img src="{{ asset('storage/'.ltrim($companyLogoPath, '/')) }}" alt="">
    </span>
  @endif

  <a class="tenant-job-card__body" href="{{ route('tenant.jobs.show', $job) }}">
    <div class="tenant-job-card__main">
      @if($companyName)
        <p class="tenant-job-card__company">{{ $companyName }}</p>
      @endif

      <h3>{{ $job->title }}</h3>

      @if($employmentTypes->isNotEmpty() || $job->salary_range)
        <p class="tenant-job-card__summary">
          {{ $employmentTypes->isNotEmpty() ? $employmentTypes->implode(', ') : 'Job type open' }}
          @if($job->salary_range)
            - {{ $job->salary_range }}
          @endif
        </p>
      @endif
    </div>

    <div class="tenant-job-card__side">
      <div class="tenant-job-card__meta">
        @if($job->location)
          <span>
            <i class="ph ph-globe-hemisphere-west" aria-hidden="true"></i>
            {{ $job->location }}
          </span>
        @endif

        @if($daysAgo)
          <span>
            <i class="ph ph-calendar-blank" aria-hidden="true"></i>
            {{ $daysAgo }}d
          </span>
        @endif
      </div>

      @if($tagLabels->isNotEmpty())
        <div class="tenant-job-card__tags">
          @foreach($tagLabels as $tag)
            <span>{{ $tag }}</span>
          @endforeach
        </div>
      @endif
    </div>
  </a>
</article>

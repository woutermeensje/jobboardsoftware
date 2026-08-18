@php
  $company = $job->relationLoaded('company') ? $job->company : null;
  $companyName = $job->company_name ?: ($company?->name ?? null);
  $companyLogoPath = $job->company_logo_path ?: ($company?->logo_path ?? null);
  $companyLogoUrl = \App\Support\PublicUploadStorage::url($companyLogoPath);
  $publishedAt = $job->published_at ?? $job->created_at;
  $postedLabel = $publishedAt ? $publishedAt->format('F j, Y') : null;
  $logoInitial = mb_strtoupper(mb_substr((string) ($companyName ?: $job->title), 0, 1));
  $employmentTypes = collect(explode(',', (string) $job->employment_type))
    ->map(fn (string $type): string => trim($type))
    ->filter()
    ->unique(fn (string $type): string => mb_strtolower($type))
    ->values();
@endphp

<article class="tenant-job-card">
  @if($postedLabel)
    <span class="tenant-job-card__posted">
      <i class="ph ph-calendar-blank" aria-hidden="true"></i>
      {{ $postedLabel }}
    </span>
  @endif

  <a class="tenant-job-card__body" href="{{ route('tenant.jobs.show', $job) }}">
    <span class="tenant-job-card__logo @unless($companyLogoUrl) tenant-job-card__logo--empty @endunless" aria-hidden="true">
      <span class="tenant-job-card__logo-text">{{ $logoInitial }}</span>
      @if($companyLogoUrl)
        <img
          src="{{ $companyLogoUrl }}"
          alt=""
          onerror="this.hidden = true; this.parentElement.classList.add('tenant-job-card__logo--empty');"
        >
      @endif
    </span>

    <div class="tenant-job-card__main">
      <h3>{{ $job->title }}</h3>

      @if($companyName || $job->location)
        <div class="tenant-job-card__meta">
          @if($companyName)
            <span>
              <i class="ph ph-buildings" aria-hidden="true"></i>
              {{ $companyName }}
            </span>
          @endif

          @if($job->location)
            <span>
              <i class="ph ph-map-pin" aria-hidden="true"></i>
              {{ $job->location }}
            </span>
          @endif
        </div>
      @endif

      @if($employmentTypes->isNotEmpty())
        <div class="tenant-job-card__tags">
          @foreach($employmentTypes as $type)
            <span>{{ $type }}</span>
          @endforeach
        </div>
      @endif
    </div>

    <span class="tenant-job-card__cta" aria-hidden="true">
      <i class="ph ph-arrow-right"></i>
    </span>
  </a>
</article>

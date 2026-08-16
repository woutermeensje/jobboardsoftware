@php
  $company = $job->relationLoaded('company') ? $job->company : null;
  $companyName = $job->company_name ?: ($company?->name ?? null);
  $companyLogoPath = $job->company_logo_path ?: ($company?->logo_path ?? null);
  $companyLogoUrl = \App\Support\PublicUploadStorage::url($companyLogoPath);
  $publishedAt = $job->published_at ?? $job->created_at;
  $daysAgo = $publishedAt ? max(1, (int) $publishedAt->diffInDays(now())) : null;
  $postedLabel = $daysAgo ? ($daysAgo === 1 ? '1 day ago' : $daysAgo.' days ago') : null;
  $logoInitial = mb_strtoupper(mb_substr((string) ($companyName ?: $job->title), 0, 1));
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

<article class="tenant-job-card">
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
      @if($companyName)
        <p class="tenant-job-card__company">{{ $companyName }}</p>
      @endif

      <h3>{{ $job->title }}</h3>

      @if($job->location || $postedLabel || $job->salary_range)
        <div class="tenant-job-card__meta">
          @if($job->location)
            <span>
              <i class="ph ph-map-pin" aria-hidden="true"></i>
              {{ $job->location }}
            </span>
          @endif

          @if($postedLabel)
            <span>
              <i class="ph ph-calendar-blank" aria-hidden="true"></i>
              {{ $postedLabel }}
            </span>
          @endif

          @if($job->salary_range)
            <span>
              <i class="ph ph-wallet" aria-hidden="true"></i>
              {{ $job->salary_range }}
            </span>
          @endif
        </div>
      @endif
    </div>

    <div class="tenant-job-card__side">
      @if($tagLabels->isNotEmpty())
        <div class="tenant-job-card__tags">
          @foreach($tagLabels as $tag)
            <span>{{ $tag }}</span>
          @endforeach
        </div>
      @endif

      <span class="tenant-job-card__cta" aria-hidden="true">
        <i class="ph ph-arrow-right"></i>
      </span>
    </div>
  </a>
</article>

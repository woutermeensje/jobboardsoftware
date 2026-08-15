<article class="tenant-job-card">
  <a class="tenant-job-card__body" href="{{ route('tenant.jobs.show', $job) }}">
    <div class="tenant-job-card__main">
      <h3>{{ $job->title }}</h3>

      <div class="tenant-job-card__meta">
        @if($job->department)
          <span>
            <i class="ph ph-buildings" aria-hidden="true"></i>
            {{ $job->department }}
          </span>
        @endif

        @if($job->location)
          <span>
            <i class="ph ph-map-pin" aria-hidden="true"></i>
            {{ $job->location }}
          </span>
        @endif

        @if($job->published_at)
          <span>
            <i class="ph ph-calendar-blank" aria-hidden="true"></i>
            {{ $job->published_at->format('d-m-Y') }}
          </span>
        @endif
      </div>

      @if($job->intro)
        <p>{{ $job->intro }}</p>
      @endif

      <div class="tenant-job-card__tags">
        @if($job->department)
          <span>{{ $job->department }}</span>
        @endif

        @if($job->employment_type)
          <span class="tenant-job-card__tag--alt">{{ $job->employment_type }}</span>
        @endif

        @if($job->salary_range)
          <span>{{ $job->salary_range }}</span>
        @endif
      </div>
    </div>

    <span class="tenant-job-card__chevron" aria-hidden="true"></span>
  </a>
</article>

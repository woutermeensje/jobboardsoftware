<article class="tenant-job-card">
  <div class="tenant-job-card__main">
    <div>
      @if($job->department)
        <span class="tenant-job-card__department">{{ $job->department }}</span>
      @endif

      <h3>{{ $job->title }}</h3>

      @if($job->intro)
        <p>{{ $job->intro }}</p>
      @endif
    </div>

    <dl class="tenant-job-card__meta">
      @if($job->location)
        <div>
          <dt><i class="ph ph-map-pin" aria-hidden="true"></i> Location</dt>
          <dd>{{ $job->location }}</dd>
        </div>
      @endif

      @if($job->employment_type)
        <div>
          <dt><i class="ph ph-clock" aria-hidden="true"></i> Type</dt>
          <dd>{{ $job->employment_type }}</dd>
        </div>
      @endif

      @if($job->salary_range)
        <div>
          <dt><i class="ph ph-currency-eur" aria-hidden="true"></i> Salary</dt>
          <dd>{{ $job->salary_range }}</dd>
        </div>
      @endif
    </dl>
  </div>

  <div class="tenant-job-card__aside">
    @if($job->published_at)
      <span>Posted {{ $job->published_at->diffForHumans() }}</span>
    @endif

    <a class="tenant-job-card__link" href="{{ route('tenant.jobs.show', $job) }}">
      View job
      <i class="ph ph-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
</article>

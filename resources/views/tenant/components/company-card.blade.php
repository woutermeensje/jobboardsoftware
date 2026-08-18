@php
  $companyName = $company->organization_name ?: $company->name;
  $companyLogoUrl = \App\Support\PublicUploadStorage::url($company->logo_path);
  $logoInitial = mb_strtoupper(mb_substr((string) $companyName, 0, 1));
  $sectorValues = $company->sectorValues();
  $jobsCount = $company->jobs_count ?? 0;
@endphp

<article class="tenant-job-card">
  <a class="tenant-job-card__body" href="{{ route('tenant.companies.show', $company) }}">
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
      <h3>{{ $companyName }}</h3>

      <div class="tenant-job-card__meta">
        @if($sectorValues !== [])
          <span>
            <i class="ph ph-buildings" aria-hidden="true"></i>
            {{ implode(', ', $sectorValues) }}
          </span>
        @endif

        <span>
          <i class="ph ph-briefcase" aria-hidden="true"></i>
          {{ $jobsCount }} {{ $jobsCount === 1 ? 'open job' : 'open jobs' }}
        </span>
      </div>
    </div>

    <span class="tenant-job-card__cta" aria-hidden="true">
      <i class="ph ph-arrow-right"></i>
    </span>
  </a>
</article>

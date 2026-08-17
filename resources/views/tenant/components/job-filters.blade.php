@php
  $selectedDepartments = $selectedDepartments ?? collect();
  $selectedEmploymentTypes = $selectedEmploymentTypes ?? collect();
  $hasActiveFilters = request()->filled('search')
    || request()->filled('location')
    || $selectedDepartments->isNotEmpty()
    || $selectedEmploymentTypes->isNotEmpty();
@endphp

<form class="tenant-job-filters" method="GET" action="{{ route('tenant.jobs') }}" data-tenant-job-filter-form>
  <div class="tenant-job-filters__grid">
    <label class="tenant-job-filters__field tenant-job-filters__field--search">
      <span class="sr-only">Search term</span>
      <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
      <input
        name="search"
        type="search"
        value="{{ request('search') }}"
        placeholder="Job title, team or topic.."
        autocomplete="off"
        data-tenant-auto-submit
      >
    </label>

    <label class="tenant-job-filters__field tenant-job-filters__field--location">
      <span class="sr-only">Location</span>
      <i class="ph ph-map-pin" aria-hidden="true"></i>
      <input
        name="location"
        type="text"
        value="{{ request('location') }}"
        placeholder="City or location"
        autocomplete="off"
        data-tenant-auto-submit
      >
    </label>

    <button class="tenant-job-filters__submit" type="submit" aria-label="Search jobs">
      <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
      <span>Search</span>
    </button>
  </div>

  @foreach($selectedDepartments as $department)
    <input type="hidden" name="department[]" value="{{ $department }}">
  @endforeach

  @foreach($selectedEmploymentTypes as $employmentType)
    <input type="hidden" name="employment_type[]" value="{{ $employmentType }}">
  @endforeach

  @if($hasActiveFilters)
    <div class="tenant-active-filters" aria-label="Active filters">
      @if(request()->filled('search'))
        <span class="tenant-active-filter">Search: {{ request('search') }}</span>
      @endif

      @if(request()->filled('location'))
        <span class="tenant-active-filter">Location: {{ request('location') }}</span>
      @endif

      @foreach($selectedDepartments as $department)
        <span class="tenant-active-filter">{{ $department }}</span>
      @endforeach

      @foreach($selectedEmploymentTypes as $employmentType)
        <span class="tenant-active-filter">{{ $employmentType }}</span>
      @endforeach
    </div>
  @endif

  <noscript>
    <div class="tenant-job-filters__fallback">
      <button class="tenant-btn tenant-btn--primary" type="submit">Apply filters</button>
    </div>
  </noscript>
</form>

@php
  $selectedDepartments = $selectedDepartments ?? collect();
  $selectedEmploymentTypes = $selectedEmploymentTypes ?? collect();
  $selectedSectors = $selectedSectors ?? collect();
  $selectedOrganizationTypes = $selectedOrganizationTypes ?? collect();
  $hasActiveFilters = request()->filled('search')
    || request()->filled('location')
    || $selectedDepartments->isNotEmpty()
    || $selectedEmploymentTypes->isNotEmpty()
    || $selectedSectors->isNotEmpty()
    || $selectedOrganizationTypes->isNotEmpty();
  $filterUrl = function (array $remove = [], array $replace = []): string {
    $query = request()->query();

    foreach ($remove as $key) {
      unset($query[$key]);
    }

    foreach ($replace as $key => $value) {
      if ($value === null || $value === '' || $value === []) {
        unset($query[$key]);
      } else {
        $query[$key] = $value;
      }
    }

    unset($query['page']);

    $queryString = http_build_query($query);

    return route('tenant.jobs').($queryString ? '?'.$queryString : '').'#jobs';
  };
  $departmentFilterUrl = fn (string $department): string => $filterUrl([], [
    'department' => $selectedDepartments
      ->reject(fn (string $selectedDepartment): bool => $selectedDepartment === $department)
      ->values()
      ->all(),
  ]);
  $employmentTypeFilterUrl = fn (string $employmentType): string => $filterUrl([], [
    'employment_type' => $selectedEmploymentTypes
      ->reject(fn (string $selectedEmploymentType): bool => $selectedEmploymentType === $employmentType)
      ->values()
      ->all(),
  ]);
  $sectorFilterUrl = fn (string $sector): string => $filterUrl([], [
    'sector' => $selectedSectors
      ->reject(fn (string $selectedSector): bool => $selectedSector === $sector)
      ->values()
      ->all(),
  ]);
  $organizationTypeFilterUrl = fn (string $organizationType): string => $filterUrl([], [
    'organization_type' => $selectedOrganizationTypes
      ->reject(fn (string $selectedOrganizationType): bool => $selectedOrganizationType === $organizationType)
      ->values()
      ->all(),
  ]);
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

  @foreach($selectedSectors as $sector)
    <input type="hidden" name="sector[]" value="{{ $sector }}">
  @endforeach

  @foreach($selectedOrganizationTypes as $organizationType)
    <input type="hidden" name="organization_type[]" value="{{ $organizationType }}">
  @endforeach

  @if($hasActiveFilters)
    <div class="tenant-active-filters" aria-label="Active filters">
      @if(request()->filled('search'))
        <a class="tenant-active-filter" href="{{ $filterUrl(['search']) }}" aria-label="Remove search filter {{ request('search') }}">
          <span>Search: {{ request('search') }}</span>
          <i class="ph ph-x" aria-hidden="true"></i>
        </a>
      @endif

      @if(request()->filled('location'))
        <a class="tenant-active-filter" href="{{ $filterUrl(['location']) }}" aria-label="Remove location filter {{ request('location') }}">
          <span>Location: {{ request('location') }}</span>
          <i class="ph ph-x" aria-hidden="true"></i>
        </a>
      @endif

      @foreach($selectedDepartments as $department)
        <a class="tenant-active-filter" href="{{ $departmentFilterUrl($department) }}" aria-label="Remove category filter {{ $department }}">
          <span>{{ $department }}</span>
          <i class="ph ph-x" aria-hidden="true"></i>
        </a>
      @endforeach

      @foreach($selectedEmploymentTypes as $employmentType)
        <a class="tenant-active-filter" href="{{ $employmentTypeFilterUrl($employmentType) }}" aria-label="Remove job type filter {{ $employmentType }}">
          <span>{{ $employmentType }}</span>
          <i class="ph ph-x" aria-hidden="true"></i>
        </a>
      @endforeach

      @foreach($selectedSectors as $sector)
        <a class="tenant-active-filter" href="{{ $sectorFilterUrl($sector) }}" aria-label="Remove sector filter {{ $sector }}">
          <span>Sector: {{ $sector }}</span>
          <i class="ph ph-x" aria-hidden="true"></i>
        </a>
      @endforeach

      @foreach($selectedOrganizationTypes as $organizationType)
        <a class="tenant-active-filter" href="{{ $organizationTypeFilterUrl($organizationType) }}" aria-label="Remove organization type filter {{ $organizationType }}">
          <span>Organization type: {{ $organizationType }}</span>
          <i class="ph ph-x" aria-hidden="true"></i>
        </a>
      @endforeach
    </div>
  @endif

  <noscript>
    <div class="tenant-job-filters__fallback">
      <button class="tenant-btn tenant-btn--primary" type="submit">Apply filters</button>
    </div>
  </noscript>
</form>

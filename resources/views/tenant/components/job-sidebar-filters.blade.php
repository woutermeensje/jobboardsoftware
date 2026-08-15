<form method="GET" action="{{ route('tenant.jobs') }}" class="tenant-sidebar-filter-form">
  @if(request()->filled('search'))
    <input type="hidden" name="search" value="{{ request('search') }}">
  @endif

  @if(request()->filled('location'))
    <input type="hidden" name="location" value="{{ request('location') }}">
  @endif

  <details class="tenant-sidebar-filter-group" open>
    <summary class="tenant-sidebar-filter-group__header">
      <span>Category</span>
      <i class="ph ph-caret-down" aria-hidden="true"></i>
    </summary>

    <div class="tenant-sidebar-filter-group__body">
      @forelse($departments as $department)
        <label class="tenant-sidebar-filter-option">
          <input
            type="checkbox"
            name="department[]"
            value="{{ $department }}"
            @checked($selectedDepartments->contains($department))
            onchange="this.form.submit()"
          >
          <span class="tenant-sidebar-filter-option__label">{{ $department }}</span>
          @if(($departmentCounts[$department] ?? 0) > 0)
            <span class="tenant-sidebar-filter-count">{{ $departmentCounts[$department] }}</span>
          @endif
        </label>
      @empty
        <p class="tenant-sidebar-filter-empty">No categories available.</p>
      @endforelse
    </div>
  </details>

  <details class="tenant-sidebar-filter-group" open>
    <summary class="tenant-sidebar-filter-group__header">
      <span>Job type</span>
      <i class="ph ph-caret-down" aria-hidden="true"></i>
    </summary>

    <div class="tenant-sidebar-filter-group__body">
      @forelse($employmentTypes as $employmentType)
        <label class="tenant-sidebar-filter-option">
          <input
            type="checkbox"
            name="employment_type[]"
            value="{{ $employmentType }}"
            @checked($selectedEmploymentTypes->contains($employmentType))
            onchange="this.form.submit()"
          >
          <span class="tenant-sidebar-filter-option__label">{{ $employmentType }}</span>
          @if(($employmentTypeCounts[$employmentType] ?? 0) > 0)
            <span class="tenant-sidebar-filter-count">{{ $employmentTypeCounts[$employmentType] }}</span>
          @endif
        </label>
      @empty
        <p class="tenant-sidebar-filter-empty">No job types available.</p>
      @endforelse
    </div>
  </details>

  <noscript>
    <button class="tenant-btn tenant-btn--primary" type="submit">Apply filters</button>
  </noscript>

  @if($hasActiveJobFilters)
    <a href="{{ route('tenant.jobs') }}#jobs" class="tenant-sidebar-filter-reset">
      <i class="ph ph-x" aria-hidden="true"></i>
      Clear filters
    </a>
  @endif
</form>

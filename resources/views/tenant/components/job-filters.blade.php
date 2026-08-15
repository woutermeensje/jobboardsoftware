@php
  $hasActiveFilters = request()->filled('search')
    || request()->filled('department')
    || request()->filled('location')
    || request()->filled('employment_type');
@endphp

<form class="tenant-job-filters" method="GET" action="{{ route('tenant.jobs') }}">
  <label class="tenant-job-filters__search">
    <span>Search jobs</span>
    <input
      name="search"
      value="{{ request('search') }}"
      placeholder="Job title, team or keyword"
      autocomplete="off"
    >
  </label>

  <label>
    <span>Department</span>
    <select name="department">
      <option value="">All departments</option>
      @foreach($departments as $department)
        <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
      @endforeach
    </select>
  </label>

  <label>
    <span>Location</span>
    <select name="location">
      <option value="">All locations</option>
      @foreach($locations as $location)
        <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
      @endforeach
    </select>
  </label>

  <label>
    <span>Type</span>
    <select name="employment_type">
      <option value="">All types</option>
      @foreach($employmentTypes as $employmentType)
        <option value="{{ $employmentType }}" @selected(request('employment_type') === $employmentType)>{{ $employmentType }}</option>
      @endforeach
    </select>
  </label>

  <div class="tenant-job-filters__actions">
    <button class="tenant-btn tenant-btn--primary" type="submit">
      <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
      Search
    </button>

    @if($hasActiveFilters)
      <a class="tenant-btn tenant-btn--ghost" href="{{ route('tenant.jobs') }}#jobs">Reset</a>
    @endif
  </div>
</form>

@extends('layouts.tenant')

@section('title', 'Job alerts | '.$brandName)
@section('meta_description', 'Get notified when new vacancies matching your interests are published on '.$brandName.'.')

@php
  $selectedEmploymentTypes = collect((array) old('employment_type', []));
  $selectedDepartments = collect((array) old('department', []));
  $selectedSectors = collect((array) old('sector', []));
  $selectedOrganizationTypes = collect((array) old('organization_type', []));
@endphp

@section('content')
  <main class="tenant-shell">
    @if(session('status'))
      <section class="tenant-alert">{{ session('status') }}</section>
    @endif

    @if($errors->any())
      <section class="tenant-alert tenant-alert--danger">
        <strong>Job alert could not be saved.</strong>
        <ul class="tenant-message-list">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </section>
    @endif

    <section class="tenant-post-job">
      <div class="tenant-post-job__content">
        <article class="tenant-panel tenant-post-job__main">
          <form method="POST" action="{{ route('tenant.job-alerts.store') }}" class="tenant-form">
            @csrf

            <div class="tenant-panel__head tenant-form-header">
              <h2 class="tenant-form-title">Create a job alert</h2>
              <p class="tenant-form-intro">Choose the categories you're interested in and we'll let you know when a matching job is published on {{ $brandName }}.</p>
            </div>

            <label>
              Email address
              <input name="email" type="email" value="{{ old('email') }}" required autofocus>
              @error('email')<span class="tenant-form__error">{{ $message }}</span>@enderror
            </label>

            <div class="tenant-job-alerts-groups">
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
                      >
                      <span class="tenant-sidebar-filter-option__label">{{ $employmentType }}</span>
                    </label>
                  @empty
                    <p class="tenant-sidebar-filter-empty">No job types available.</p>
                  @endforelse
                </div>
              </details>

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
                      >
                      <span class="tenant-sidebar-filter-option__label">{{ $department }}</span>
                    </label>
                  @empty
                    <p class="tenant-sidebar-filter-empty">No categories available.</p>
                  @endforelse
                </div>
              </details>

              <details class="tenant-sidebar-filter-group" open>
                <summary class="tenant-sidebar-filter-group__header">
                  <span>Sector</span>
                  <i class="ph ph-caret-down" aria-hidden="true"></i>
                </summary>

                <div class="tenant-sidebar-filter-group__body">
                  @forelse($sectors as $sector)
                    <label class="tenant-sidebar-filter-option">
                      <input
                        type="checkbox"
                        name="sector[]"
                        value="{{ $sector }}"
                        @checked($selectedSectors->contains($sector))
                      >
                      <span class="tenant-sidebar-filter-option__label">{{ $sector }}</span>
                    </label>
                  @empty
                    <p class="tenant-sidebar-filter-empty">No sectors available.</p>
                  @endforelse
                </div>
              </details>

              <details class="tenant-sidebar-filter-group" open>
                <summary class="tenant-sidebar-filter-group__header">
                  <span>Organization type</span>
                  <i class="ph ph-caret-down" aria-hidden="true"></i>
                </summary>

                <div class="tenant-sidebar-filter-group__body">
                  @forelse($organizationTypes as $organizationType)
                    <label class="tenant-sidebar-filter-option">
                      <input
                        type="checkbox"
                        name="organization_type[]"
                        value="{{ $organizationType }}"
                        @checked($selectedOrganizationTypes->contains($organizationType))
                      >
                      <span class="tenant-sidebar-filter-option__label">{{ $organizationType }}</span>
                    </label>
                  @empty
                    <p class="tenant-sidebar-filter-empty">No organization types available.</p>
                  @endforelse
                </div>
              </details>
            </div>

            <button class="tenant-btn tenant-btn--primary" type="submit">Create job alert</button>
          </form>
        </article>
      </div>
    </section>
  </main>
@endsection

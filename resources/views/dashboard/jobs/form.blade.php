@extends('layouts.app')

@section('title', 'Edit job | JobBoardSoftware')
@section('meta_description', 'Create or edit a job for a tenant job board.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation', ['activeTenant' => $tenant])

    <div class="dash-content">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">{{ $tenant->name }}</p>
        <h1 class="dash-title">{{ $job->exists ? 'Edit job' : 'New job' }}</h1>
        <p class="dash-subtitle">This job appears on the customer domain once its status is set to published.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ ucfirst($job->status ?? 'draft') }}</strong>
        <span>{{ $tenant->slug }}</span>
      </aside>
    </header>

    <section class="dash-panel">
      <form class="form tenant-job-form" method="POST" action="{{ $action }}">
        @csrf
        @if($method === 'PUT')
          @method('PUT')
        @endif

        <div class="form-grid form-grid--two">
          <div class="form-field">
            <label class="form-label" for="title">Title</label>
            <input class="form-control" id="title" name="title" type="text" value="{{ old('title', $job->title) }}" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div class="form-field">
            <label class="form-label" for="slug">Slug</label>
            <input class="form-control" id="slug" name="slug" type="text" value="{{ old('slug', $job->slug) }}" placeholder="generated from the title automatically">
            @error('slug')<p class="form-error">{{ $message }}</p>@enderror
          </div>
        </div>

        <div class="form-grid form-grid--three">
          <div class="form-field">
            <label class="form-label" for="department">Department</label>
            <input class="form-control" id="department" name="department" type="text" value="{{ old('department', $job->department) }}">
          </div>
          <div class="form-field">
            <label class="form-label" for="location">Location</label>
            <input class="form-control" id="location" name="location" type="text" value="{{ old('location', $job->location) }}">
          </div>
          <div class="form-field">
            <label class="form-label" for="employment_type">Employment type</label>
            <input class="form-control" id="employment_type" name="employment_type" type="text" value="{{ old('employment_type', $job->employment_type) }}" placeholder="Fulltime">
          </div>
        </div>

        <div class="form-grid form-grid--two">
          <div class="form-field">
            <label class="form-label" for="salary_range">Salary range</label>
            <input class="form-control" id="salary_range" name="salary_range" type="text" value="{{ old('salary_range', $job->salary_range) }}">
          </div>
          <div class="form-field">
            <label class="form-label" for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
              @foreach([\App\Models\TenantJob::STATUS_DRAFT => 'Draft', \App\Models\TenantJob::STATUS_PUBLISHED => 'Published', \App\Models\TenantJob::STATUS_CLOSED => 'Closed'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $job->status) === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-field">
          <label class="form-label" for="intro">Short intro</label>
          <textarea class="form-control" id="intro" name="intro" rows="3">{{ old('intro', $job->intro) }}</textarea>
        </div>

        <div class="form-field">
          <label class="form-label" for="description">Job description</label>
          <textarea class="form-control quill-ready" id="description" name="description" rows="10">{{ old('description', $job->description) }}</textarea>
          <p class="form-help">This field already uses the base styling for rich text editors.</p>
        </div>

        <div class="form-actions">
          <button class="dash-btn dash-btn--primary" type="submit">Save</button>
          <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.jobs.index', $tenant) }}">Back</a>
        </div>
      </form>
    </section>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
@endpush

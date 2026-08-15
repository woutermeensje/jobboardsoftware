@extends('layouts.app')

@section('title', 'Applications | JobBoardSoftware admin')
@section('meta_description', 'Manage tenant job applications across the platform.')
@section('layout', 'dashboard')
@section('dashboard_label', 'Admin')
@section('dashboard_title', 'Applications')
@section('dashboard_subtitle', 'Review incoming candidate applications across tenant job boards.')
@section('dashboard_sidebar')
  @include('admin.partials.navigation')
@endsection

@php
  $applicationStatuses = [
    \App\Models\JobApplication::STATUS_NEW => 'New',
    \App\Models\JobApplication::STATUS_REVIEWED => 'Reviewed',
    \App\Models\JobApplication::STATUS_REJECTED => 'Rejected',
    \App\Models\JobApplication::STATUS_HIRED => 'Hired',
  ];
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>All applications</h2>
            <p>Track candidate status without switching to each tenant dashboard.</p>
          </div>
        </div>

        <table class="dash-table">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Job</th>
              <th>Tenant</th>
              <th>Status</th>
              <th>Manage</th>
            </tr>
          </thead>
          <tbody>
            @forelse($applications as $application)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $application->name }}</span>
                  <span class="dash-cell-meta">{{ $application->email }}</span>
                  <span class="dash-cell-meta">{{ $application->phone ?: 'No phone' }} - CV: {{ $application->cv_path ? 'Yes' : 'No' }}</span>
                </td>
                <td>
                  <span class="dash-cell-title">{{ $application->job?->title ?? 'Deleted job' }}</span>
                  <span class="dash-cell-meta">{{ $application->job?->slug ?? 'Unknown' }}</span>
                </td>
                <td>
                  <span class="dash-cell-title">{{ $application->tenant?->name ?? $application->tenant_id }}</span>
                  <span class="dash-cell-meta">{{ $application->tenant?->owner?->email ?? 'Unknown owner' }}</span>
                </td>
                <td><span class="dash-status">{{ ucfirst($application->status) }}</span></td>
                <td>
                  <form class="admin-table-form" method="POST" action="{{ route('admin.applications.update', $application) }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-form-grid">
                      <select class="form-control" name="status" aria-label="Application status" required>
                        @foreach($applicationStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('status', $application->status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <button class="dash-btn dash-btn--primary" type="submit">Save application</button>
                    </div>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5">No applications yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </section>
@endsection

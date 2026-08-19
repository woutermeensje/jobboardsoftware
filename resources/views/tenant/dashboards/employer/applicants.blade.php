@extends('layouts.app')

@section('title', 'Applicants | '.$brandName)
@section('meta_description', 'Latest candidate applications for your jobs.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.employer-navigation')
@endsection

@section('content')
    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel" aria-labelledby="applicants-title">
      <div class="dash-panel__head">
        <div>
          <h2 id="applicants-title">Applicants</h2>
          <p>Latest candidate applications for your jobs.</p>
        </div>
      </div>

      <table class="dash-table">
        <thead>
          <tr>
            <th>Candidate</th>
            <th>Job</th>
            <th>Status</th>
            <th>Received</th>
          </tr>
        </thead>
        <tbody>
          @forelse($applications as $application)
            <tr>
              <td>
                <span class="dash-cell-title">{{ $application->name }}</span>
                <span class="dash-cell-meta">{{ $application->email }}</span>
              </td>
              <td>{{ $application->job?->title ?? 'Deleted job' }}</td>
              <td><span class="dash-status dash-status--accent">{{ ucfirst($application->status) }}</span></td>
              <td>{{ $application->created_at?->format('Y-m-d') }}</td>
            </tr>
          @empty
            <tr><td colspan="4">No applications yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </section>
@endsection

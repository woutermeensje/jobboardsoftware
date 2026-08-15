@extends('layouts.app')

@section('title', 'Job seeker dashboard | '.$brandName)
@section('meta_description', 'Tenant job seeker dashboard for applications, recommended jobs and profile progress.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.jobseeker-navigation')
@endsection

@section('content')
    <div class="dash-stats">
      @foreach($stats as $stat)
        <article class="dash-stat">
          <span>{{ $stat['label'] }}</span>
          <strong>{{ $stat['value'] }}</strong>
        </article>
      @endforeach
    </div>

    <div class="dash-layout">
      <main class="dash-main">
        <section class="dash-panel" aria-labelledby="recommended-jobs-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="recommended-jobs-title">Recommended jobs</h2>
              <p>Open roles from {{ $brandName }} that may match your next step.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs') }}">
              <i class="ph ph-magnifying-glass"></i>
              Search jobs
            </a>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Department</th>
                <th>Location</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recommendedJobs as $job)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $job->title }}</span>
                    <span class="dash-cell-meta">{{ $job->employment_type ?: 'Employment type open' }}</span>
                  </td>
                  <td>{{ $job->department ?: 'General' }}</td>
                  <td>{{ $job->location ?: 'Location open' }}</td>
                  <td><a class="dash-btn dash-btn--ghost" href="{{ route('tenant.jobs.show', $job) }}">View</a></td>
                </tr>
              @empty
                <tr><td colspan="4">No open jobs yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </section>

        <section class="dash-panel" id="applications" aria-labelledby="applications-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="applications-title">Applications</h2>
              <p>Applications connected to your email address on this job board.</p>
            </div>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Status</th>
                <th>Submitted</th>
              </tr>
            </thead>
            <tbody>
              @forelse($applications as $application)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $application->job?->title ?? 'Deleted job' }}</span>
                    <span class="dash-cell-meta">{{ $application->job?->location ?? $brandName }}</span>
                  </td>
                  <td><span class="dash-status dash-status--accent">{{ ucfirst($application->status) }}</span></td>
                  <td>{{ $application->created_at?->format('Y-m-d') }}</td>
                </tr>
              @empty
                <tr><td colspan="3">No applications yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card" id="profile">
          <h2>Profile</h2>
          <p>Your tenant account is connected to {{ $brandName }}.</p>
          <ul class="dash-list">
            <li>
              <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->email }}</span>
              </div>
              <span>Job seeker</span>
            </li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Profile progress</h2>
          <p>A complete profile will make matching and applications easier.</p>
          <div class="dash-progress" aria-label="Profile progress">
            <div class="dash-progress__track"><span class="dash-progress__bar dash-progress__bar--candidate"></span></div>
            <span class="dash-cell-meta">74% complete</span>
          </div>
          <ul class="dash-checklist">
            <li><i class="ph ph-check-circle"></i>Account created</li>
            <li><i class="ph ph-check-circle"></i>Email address saved</li>
            <li><i class="ph ph-circle"></i>Upload CV</li>
            <li><i class="ph ph-circle"></i>Add preferred locations</li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Quick actions</h2>
          <p>Continue with the most common candidate actions.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs') }}">Search jobs</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.home') }}">View job board</a>
          </div>
        </section>
      </aside>
    </div>
@endsection

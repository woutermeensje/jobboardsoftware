@extends('layouts.app')

@section('title', 'Employer dashboard | '.$brandName)
@section('meta_description', 'Tenant employer dashboard for jobs, applications and company profile activity.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.employer-navigation')
@endsection

@section('content')
    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

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
        <section class="dash-panel" id="jobs" aria-labelledby="jobs-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="jobs-title">Jobs</h2>
              <p>Current jobs and application counts for your employer account.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs') }}">
              <i class="ph ph-briefcase"></i>
              View jobs
            </a>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Status</th>
                <th>Applications</th>
                <th>Published</th>
              </tr>
            </thead>
            <tbody>
              @forelse($jobs as $job)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $job->title }}</span>
                    <span class="dash-cell-meta">{{ $job->location ?: 'No location' }}</span>
                  </td>
                  <td><span class="dash-status">{{ ucfirst($job->status) }}</span></td>
                  <td>{{ $job->applications_count }}</td>
                  <td>{{ $job->published_at?->format('Y-m-d') ?? 'Not published' }}</td>
                </tr>
              @empty
                <tr><td colspan="4">No jobs yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </section>

        <section class="dash-panel" id="applications" aria-labelledby="employer-applications-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="employer-applications-title">Applications</h2>
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
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card" id="company">
          <h2>Company profile</h2>
          <p>Your employer account is connected to {{ $brandName }}.</p>
          <ul class="dash-list">
            <li>
              <div>
                <strong>{{ $user->company_name ?: $user->name }}</strong>
                <span>{{ $user->email }}</span>
              </div>
              <span>Employer</span>
            </li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Setup progress</h2>
          <p>Complete the employer profile before opening a full posting flow.</p>
          <div class="dash-progress" aria-label="Employer setup progress">
            <div class="dash-progress__track"><span class="dash-progress__bar dash-progress__bar--employer"></span></div>
            <span class="dash-cell-meta">68% complete</span>
          </div>
          <ul class="dash-checklist">
            <li><i class="ph ph-check-circle"></i>Account created</li>
            <li><i class="ph ph-check-circle"></i>Company name saved</li>
            <li><i class="ph ph-circle"></i>Add billing details</li>
            <li><i class="ph ph-circle"></i>Publish first job</li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Quick actions</h2>
          <p>Review the live job board and latest candidate activity.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs') }}">View jobs</a>
            <a class="dash-btn dash-btn--ghost" href="#applications">Applications</a>
          </div>
        </section>
      </aside>
    </div>
@endsection

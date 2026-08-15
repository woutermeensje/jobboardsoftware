@extends('layouts.app')

@section('title', 'Job seeker dashboard | JobBoardSoftware')
@section('meta_description', 'Job seeker dashboard for recommended jobs, applications, job alerts and saved jobs.')
@section('layout', 'dashboard')
@section('dashboard_label', 'Job seeker portal')
@section('dashboard_title', 'Welcome back, '.$user->name)
@section('dashboard_subtitle', 'Track your applications, view recommended jobs and manage job alerts from your personal dashboard.')
@section('dashboard_sidebar')
  <div class="dash-nav__brand">
    <span>JS</span>
    <div>
      <strong>Job seeker</strong>
      <small>{{ $user->email }}</small>
    </div>
  </div>

  <nav class="dash-nav__links" aria-label="Job seeker navigation">
    <a class="is-active" href="{{ route('werkzoekende.dashboard') }}">
      <i class="ph ph-squares-four"></i>
      Dashboard
    </a>
    <a href="{{ route('welcome') }}#jobs">
      <i class="ph ph-magnifying-glass"></i>
      Search jobs
    </a>
    <a href="{{ route('pages.job-alerts') }}">
      <i class="ph ph-bell"></i>
      Job alerts
    </a>
    <a href="{{ route('pages.nieuwsbrief') }}">
      <i class="ph ph-envelope"></i>
      Newsletter
    </a>
    <a href="{{ route('welcome') }}">
      <i class="ph ph-house"></i>
      Website
    </a>
  </nav>
@endsection

@php
  $stats = [
    ['label' => 'New matches', 'value' => '12'],
    ['label' => 'Saved jobs', 'value' => '7'],
    ['label' => 'Applications', 'value' => '4'],
    ['label' => 'Active alerts', 'value' => '3'],
  ];

  $matches = [
    ['title' => 'Laravel Developer', 'company' => 'BrightApps', 'meta' => 'Amsterdam - Fulltime', 'match' => '92% match'],
    ['title' => 'Freelance UX Designer', 'company' => 'NicheJobs', 'meta' => 'Remote - Freelance', 'match' => '86% match'],
    ['title' => 'Operations Specialist', 'company' => 'WorkGrid', 'meta' => 'Eindhoven - Parttime', 'match' => '78% match'],
  ];

  $applications = [
    ['title' => 'Customer Success Manager', 'company' => 'HirePilot', 'status' => 'Interview scheduled', 'updated' => 'Today'],
    ['title' => 'Recruitment Marketer', 'company' => 'TalentFlow', 'status' => 'In review', 'updated' => 'Yesterday'],
    ['title' => 'Sales Development Representative', 'company' => 'BoardWorks', 'status' => 'Submitted', 'updated' => '3 days ago'],
  ];
@endphp

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
        <section class="dash-panel" aria-labelledby="candidate-matches-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="candidate-matches-title">Recommended jobs</h2>
              <p>Jobs that match your profile, location and preferences.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('welcome') }}#jobs">
              <i class="ph ph-magnifying-glass"></i>
              Search jobs
            </a>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Employer</th>
                <th>Match</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($matches as $match)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $match['title'] }}</span>
                    <span class="dash-cell-meta">{{ $match['meta'] }}</span>
                  </td>
                  <td>{{ $match['company'] }}</td>
                  <td><span class="dash-status">{{ $match['match'] }}</span></td>
                  <td><a class="dash-btn dash-btn--ghost" href="{{ route('welcome') }}#jobs">View</a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>

        <section class="dash-panel" aria-labelledby="candidate-applications-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="candidate-applications-title">Applications</h2>
              <p>Statuses and latest activity for your applications.</p>
            </div>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Status</th>
                <th>Updated</th>
              </tr>
            </thead>
            <tbody>
              @foreach($applications as $application)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $application['title'] }}</span>
                    <span class="dash-cell-meta">{{ $application['company'] }}</span>
                  </td>
                  <td><span class="dash-status dash-status--accent">{{ $application['status'] }}</span></td>
                  <td>{{ $application['updated'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card">
          <h2>Quick actions</h2>
          <p>Jump straight to the most important job seeker flows.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('pages.job-alerts') }}">Create job alert</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('pages.nieuwsbrief') }}">Newsletter</a>
          </div>
        </section>

        <section class="dash-card">
          <h2>Profile progress</h2>
          <p>A more complete profile makes better matches possible.</p>
          <div class="dash-progress" aria-label="Profile progress">
            <div class="dash-progress__track"><span class="dash-progress__bar dash-progress__bar--candidate"></span></div>
            <span class="dash-cell-meta">74% complete</span>
          </div>
          <ul class="dash-checklist">
            <li><i class="ph ph-check-circle"></i>Account created</li>
            <li><i class="ph ph-check-circle"></i>Email address confirmed</li>
            <li><i class="ph ph-circle"></i>Upload CV</li>
            <li><i class="ph ph-circle"></i>Add preferred locations</li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Active job alerts</h2>
          <ul class="dash-list">
            <li>
              <div>
                <strong>Laravel Developer</strong>
                <span>Amsterdam + remote</span>
              </div>
              <span>Daily</span>
            </li>
            <li>
              <div>
                <strong>UX Designer</strong>
                <span>Freelance assignments</span>
              </div>
              <span>Weekly</span>
            </li>
          </ul>
        </section>

        <form method="POST" action="{{ route('logout') }}" class="dash-card">
          @csrf
          <h2>Session</h2>
          <p>You are signed in as a job seeker.</p>
          <div class="dash-actions dash-actions--spaced">
            <button class="dash-btn dash-btn--ghost" type="submit">Log out</button>
          </div>
        </form>
      </aside>
    </div>
@endsection

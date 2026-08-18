@extends($layout ?? 'layouts.app')

@php
  $pageTitle = $title ?? 'Log in';
  $brandTitle = $brandName ?? 'JobBoardSoftware';
  $jobseekerUrl = $jobseekerUrl ?? route('login.jobseeker');
  $employerUrl = $employerUrl ?? route('login.employer');
  $registerUrl = $registerUrl ?? route('register.choice');
  $backUrl = $backUrl ?? route('welcome');
  $backLabel = $backLabel ?? 'Back to website';
@endphp

@section('title', $pageTitle.' | '.$brandTitle)

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-choice-grid">
      <a class="auth-choice-card auth-choice-card--simple" href="{{ $jobseekerUrl }}">
        <h2>Job seeker</h2>
        <p>For candidates who want to save jobs and manage applications.</p>
      </a>

      <a class="auth-choice-card auth-choice-card--simple" href="{{ $employerUrl }}">
        <h2>Employer</h2>
        <p>For companies that post jobs and follow up with candidates.</p>
      </a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

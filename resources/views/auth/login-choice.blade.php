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
    @if(! empty($heading) || ! empty($subtitle))
      <div class="auth-head">
        @if(! empty($eyebrow))
          <p class="auth-eyebrow">{{ $eyebrow }}</p>
        @endif
        @if(! empty($heading))
          <h1>{{ $heading }}</h1>
        @endif
        @if(! empty($subtitle))
          <p>{{ $subtitle }}</p>
        @endif
      </div>
    @endif

    <div class="auth-choice-grid">
      <a class="auth-choice-card" href="{{ $jobseekerUrl }}">
        <i class="ph ph-user-circle"></i>
        <h2>Job seeker</h2>
        <p>For candidates who want to save jobs and manage applications.</p>
      </a>

      <a class="auth-choice-card" href="{{ $employerUrl }}">
        <i class="ph ph-buildings"></i>
        <h2>Employer</h2>
        <p>For companies that post jobs and follow up with candidates.</p>
      </a>
    </div>

    <div class="auth-choice-actions">
      <a class="auth-link" href="{{ $registerUrl }}">Create account</a>
      <a class="auth-link" href="{{ $backUrl }}">{{ $backLabel }}</a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

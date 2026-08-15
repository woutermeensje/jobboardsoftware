@extends($layout ?? 'layouts.app')

@php
  $pageTitle = $title ?? 'Sign up';
  $brandTitle = $brandName ?? 'JobBoardSoftware';
  $jobseekerUrl = $jobseekerUrl ?? route('register.jobseeker');
  $employerUrl = $employerUrl ?? route('register.employer');
  $loginUrl = $loginUrl ?? route('login.choice');
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
        <i class="ph ph-user-circle-plus"></i>
        <h2>Job seeker</h2>
        <p>Create a candidate profile for jobs, favorites and applications.</p>
      </a>

      <a class="auth-choice-card" href="{{ $employerUrl }}">
        <i class="ph ph-briefcase"></i>
        <h2>Employer</h2>
        <p>Create an employer account for job postings and applications.</p>
      </a>
    </div>

    <div class="auth-choice-actions">
      <a class="auth-link" href="{{ $loginUrl }}">I already have an account</a>
      <a class="auth-link" href="{{ $backUrl }}">{{ $backLabel }}</a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

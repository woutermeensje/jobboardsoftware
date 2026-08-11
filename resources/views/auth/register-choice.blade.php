@extends('layouts.app')

@section('title', 'Sign up | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-choice-grid">
      <a class="auth-choice-card" href="{{ route('register.werkzoekende') }}">
        <i class="ph ph-user-circle-plus"></i>
        <h2>Job seeker</h2>
        <p>Create a candidate profile for jobs, favorites and applications.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('register.werkgever') }}">
        <i class="ph ph-briefcase"></i>
        <h2>Employer</h2>
        <p>Create an employer account for job postings and applications.</p>
      </a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

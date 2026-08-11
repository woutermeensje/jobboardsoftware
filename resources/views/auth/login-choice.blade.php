@extends('layouts.app')

@section('title', 'Log in | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-choice-grid">
      <a class="auth-choice-card" href="{{ route('login.werkzoekende') }}">
        <i class="ph ph-user-circle"></i>
        <h2>Job seeker</h2>
        <p>For candidates who want to save jobs and manage applications.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('login.werkgever') }}">
        <i class="ph ph-buildings"></i>
        <h2>Employer</h2>
        <p>For companies that post jobs and follow up with candidates.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('admin.login') }}">
        <i class="ph ph-shield-check"></i>
        <h2>Admin</h2>
        <p>For platform management, user management and job moderation.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('register.choice') }}">
        <i class="ph ph-user-plus"></i>
        <h2>No account yet?</h2>
        <p>Create a job seeker or employer account.</p>
      </a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

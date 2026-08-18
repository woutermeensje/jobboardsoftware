@extends('layouts.app')

@section('title', $title.' | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-form-card">
      <div class="auth-form-head">
        <h1>Verify your email</h1>
        <p>We sent a verification link to {{ $user->email }}. Open that email to continue with your company, plan and payment setup.</p>
      </div>

      @if(session('status') === 'verification-link-sent')
        <p class="auth-notice">A new verification link has been sent.</p>
      @endif

      <div class="auth-form">
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button class="auth-button auth-button--primary" type="submit">
            <i class="ph ph-envelope-simple"></i>
            Send verification link again
          </button>
        </form>

        <div class="auth-secondary-actions">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="auth-link auth-link-button" type="submit">Log out</button>
          </form>
          <a class="auth-link" href="{{ route('welcome') }}">Back to website</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

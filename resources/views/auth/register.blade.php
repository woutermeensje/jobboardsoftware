@extends('layouts.app')

@section('title', $title.' | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-form-card">
      <div class="auth-form-head">
        <h1>Create account</h1>
      </div>

      <form method="POST" action="{{ $action }}" class="auth-form">
        @csrf

        <div class="auth-field">
          <label class="auth-label" for="name">Name</label>
          <input id="name" class="auth-input" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
          @error('name')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        @if(in_array($role, [\App\Models\User::ROLE_WERKGEVER, \App\Models\User::ROLE_TENANT_OWNER], true))
          <div class="auth-field">
            <label class="auth-label" for="company_name">{{ $companyLabel ?? 'Company name' }}</label>
            <input id="company_name" class="auth-input" name="company_name" type="text" value="{{ old('company_name') }}" autocomplete="organization" required>
            @error('company_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>
        @endif

        <div class="auth-field">
          <label class="auth-label" for="email">Email address</label>
          <input id="email" class="auth-input" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
          @error('email')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-field">
          <label class="auth-label" for="password">Password</label>
          <input id="password" class="auth-input" name="password" type="password" autocomplete="new-password" required>
          @error('password')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-field">
          <label class="auth-label" for="password_confirmation">Confirm password</label>
          <input id="password_confirmation" class="auth-input" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <div class="auth-actions">
          <button class="auth-button auth-button--primary" type="submit">
            <i class="ph ph-rocket-launch"></i>
            Create account
          </button>
          <div class="auth-secondary-actions">
            <a class="auth-link" href="{{ $loginUrl }}">I already have an account</a>
            <a class="auth-link" href="{{ route('welcome') }}">Back to website</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

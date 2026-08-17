@extends($layout ?? 'layouts.app')

@php
  $brandTitle = $brandName ?? 'JobBoardSoftware';
  $formTitle = $formTitle ?? 'Create account';
  $backUrl = $backUrl ?? route('welcome');
  $backLabel = $backLabel ?? 'Back to website';
  $requiresCompanyName = $requiresCompanyName ?? false;
@endphp

@section('title', $title.' | '.$brandTitle)

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-form-card">
      <div class="auth-form-head">
        <h1>{{ $formTitle }}</h1>
        @if(! empty($subtitle))
          <p>{{ $subtitle }}</p>
        @endif
      </div>

      <form method="POST" action="{{ $action }}" class="auth-form">
        @csrf

        <div class="auth-grid auth-grid--two">
          <div class="auth-field">
            <label class="auth-label" for="first_name">First name</label>
            <input id="first_name" class="auth-input" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" required autofocus>
            @error('first_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-field">
            <label class="auth-label" for="last_name">Last name</label>
            <input id="last_name" class="auth-input" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" required>
            @error('last_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="auth-grid auth-grid--two">
          <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <input id="email" class="auth-input" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-field">
            <label class="auth-label" for="phone_number">Phone number</label>
            <input id="phone_number" class="auth-input" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" required>
            @error('phone_number')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>
        </div>

        @if($requiresCompanyName)
          <div class="auth-field">
            <label class="auth-label" for="company_name">Company name</label>
            <input id="company_name" class="auth-input" name="company_name" type="text" value="{{ old('company_name') }}" autocomplete="organization" required>
            @error('company_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>
        @endif

        <div class="auth-field">
          <label class="auth-label" for="heard_about_us">Where did you hear about us?</label>
          <input id="heard_about_us" class="auth-input" name="heard_about_us" type="text" value="{{ old('heard_about_us') }}" required>
          @error('heard_about_us')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-grid auth-grid--two">
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
        </div>

        <div class="auth-actions">
          <button class="auth-button auth-button--primary" type="submit">
            <i class="ph ph-rocket-launch"></i>
            Create account
          </button>
          <div class="auth-secondary-actions">
            <a class="auth-link" href="{{ $loginUrl }}">I already have an account</a>
            <a class="auth-link" href="{{ $backUrl }}">{{ $backLabel }}</a>
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

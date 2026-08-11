@extends('layouts.app')

@section('title', $title.' | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-head">
      <p class="auth-eyebrow">{{ $eyebrow ?? ucfirst(str_replace('_', ' ', $role)) }}</p>
      <h1>{{ $title }}</h1>
      <p>{{ $subtitle }}</p>
    </div>

    <div class="auth-form-card">
      <form method="POST" action="{{ $action }}" class="auth-form">
        @csrf

        <div class="auth-field">
          <label class="auth-label" for="name">Naam</label>
          <input id="name" class="auth-input" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
          @error('name')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        @if(in_array($role, [\App\Models\User::ROLE_WERKGEVER, \App\Models\User::ROLE_TENANT_OWNER], true))
          <div class="auth-field">
            <label class="auth-label" for="company_name">{{ $companyLabel ?? 'Bedrijfsnaam' }}</label>
            <input id="company_name" class="auth-input" name="company_name" type="text" value="{{ old('company_name') }}" autocomplete="organization" required>
            @error('company_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>
        @endif

        <div class="auth-field">
          <label class="auth-label" for="email">E-mailadres</label>
          <input id="email" class="auth-input" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
          @error('email')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-field">
          <label class="auth-label" for="password">Wachtwoord</label>
          <input id="password" class="auth-input" name="password" type="password" autocomplete="new-password" required>
          @error('password')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-field">
          <label class="auth-label" for="password_confirmation">Wachtwoord herhalen</label>
          <input id="password_confirmation" class="auth-input" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <div class="auth-actions">
          <button class="btn btn-primary" type="submit">Account aanmaken</button>
          <a class="auth-link" href="{{ $loginUrl }}">Ik heb al een account</a>
          <a class="auth-link" href="{{ route('welcome') }}">Terug naar website</a>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush

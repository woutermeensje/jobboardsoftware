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
          <label class="auth-label" for="email">E-mailadres</label>
          <input id="email" class="auth-input" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
          @error('email')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-field">
          <label class="auth-label" for="password">Wachtwoord</label>
          <input id="password" class="auth-input" name="password" type="password" autocomplete="current-password" required>
          @error('password')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <label class="auth-check">
          <input type="checkbox" name="remember" value="1">
          Ingelogd blijven
        </label>

        <div class="auth-actions">
          <button class="btn btn-primary" type="submit">Inloggen</button>
          @if($registerUrl)
            <a class="auth-link" href="{{ $registerUrl }}">Account aanmaken</a>
          @endif
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
